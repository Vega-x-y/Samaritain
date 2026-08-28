<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\OwnerWallet;
use App\Models\Transaction;
use App\Models\WalletEntry;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OwnerWalletService
{
    public function reservePayout(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $wallet = $this->walletForOwner($transaction->user_id);
            $amount = (int) $transaction->amount;
            $existing = WalletEntry::where('transaction_id', $transaction->transaction_id)
                ->where('kind', 'payout_reservation')
                ->exists();

            if ($existing) {
                return;
            }

            if ($wallet->available_balance < $amount) {
                throw new RuntimeException('Le solde disponible est insuffisant pour ce retrait.');
            }

            $wallet->decrement('available_balance', $amount);
            $wallet->increment('reserved_balance', $amount);
            $wallet->entries()->create([
                'transaction_id' => $transaction->transaction_id,
                'kind' => 'payout_reservation',
                'amount' => $amount,
            ]);
        });
    }

    public function settle(Transaction $transaction, TransactionStatus $status): void
    {
        DB::transaction(function () use ($transaction, $status): void {
            $transaction->refresh();
            $transaction->update(['status' => $status]);

            if ($transaction->type === TransactionType::DEPOSIT && $status === TransactionStatus::COMPLETED) {
                $this->creditRentOwner($transaction);
            }

            if ($transaction->type !== TransactionType::PAYOUT) {
                return;
            }

            $wallet = $this->walletForOwner($transaction->user_id);
            $reservation = WalletEntry::where('transaction_id', $transaction->transaction_id)
                ->where('kind', 'payout_reservation')
                ->first();

            if ($status === TransactionStatus::COMPLETED && ! WalletEntry::where('transaction_id', $transaction->transaction_id)->where('kind', 'payout_debit')->exists()) {
                $amount = $reservation?->amount ?? (int) $transaction->amount;
                if ($reservation) {
                    $wallet->decrement('reserved_balance', $amount);
                } else {
                    if ($wallet->available_balance < $amount) {
                        throw new RuntimeException('Le solde disponible est insuffisant pour finaliser ce retrait.');
                    }
                    $wallet->decrement('available_balance', $amount);
                }
                $wallet->entries()->create([
                    'transaction_id' => $transaction->transaction_id,
                    'kind' => 'payout_debit',
                    'amount' => $amount,
                ]);
            }

            if (in_array($status, [TransactionStatus::FAILED, TransactionStatus::REJECTED, TransactionStatus::CANCELLED], true) && $reservation && ! WalletEntry::where('transaction_id', $transaction->transaction_id)->where('kind', 'payout_release')->exists()) {
                $wallet->increment('available_balance', $reservation->amount);
                $wallet->decrement('reserved_balance', $reservation->amount);
                $wallet->entries()->create([
                    'transaction_id' => $transaction->transaction_id,
                    'kind' => 'payout_release',
                    'amount' => $reservation->amount,
                ]);
            }
        });
    }

    public function balanceForOwner(int $ownerId): OwnerWallet
    {
        return $this->walletForOwner($ownerId);
    }

    private function creditRentOwner(Transaction $transaction): void
    {
        if (! $transaction->rentPayment) {
            return;
        }

        $ownerId = $transaction->rentPayment->contract?->property?->created_by;
        if (! $ownerId) {
            throw (new ModelNotFoundException)->setModel('property');
        }

        $wallet = $this->walletForOwner((int) $ownerId);
        $exists = WalletEntry::where('transaction_id', $transaction->transaction_id)
            ->where('kind', 'rent_credit')
            ->exists();

        if ($exists) {
            return;
        }

        $wallet->increment('available_balance', (int) $transaction->amount);
        $wallet->entries()->create([
            'transaction_id' => $transaction->transaction_id,
            'kind' => 'rent_credit',
            'amount' => (int) $transaction->amount,
        ]);
    }

    private function walletForOwner(int $ownerId): OwnerWallet
    {
        OwnerWallet::firstOrCreate(['owner_id' => $ownerId]);

        return OwnerWallet::where('owner_id', $ownerId)->lockForUpdate()->firstOrFail();
    }
}
