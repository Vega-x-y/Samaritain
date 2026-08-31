<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Artisan;
use App\Models\ArtisanWallet;
use App\Models\ArtisanWalletEntry;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ArtisanWalletService
{
    public function getWalletForArtisan(Artisan $artisan): ArtisanWallet
    {
        return ArtisanWallet::firstOrCreate(
            ['artisan_id' => $artisan->id],
            ['available_balance' => 0, 'reserved_balance' => 0]
        );
    }

    /**
     * Credit a down payment to the artisan's wallet.
     * Idempotent based on transaction_id.
     */
    public function creditDownPayment(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $request = $transaction->artisanRequest;
            if (! $request || ! $request->artisan) {
                return;
            }

            $wallet = $this->getWalletForArtisan($request->artisan);

            // Check if already credited
            $existingEntry = $wallet->entries()->where('transaction_id', $transaction->transaction_id)->first();
            if ($existingEntry) {
                return; // Already processed
            }

            $amount = $transaction->amount;
            $commissionPercent = Setting::getValue('artisan_commission_percent', 5);

            $commission = (int) round($amount * ($commissionPercent / 100));
            $netAmount = $amount - $commission;

            $wallet->entries()->create([
                'transaction_id' => $transaction->transaction_id,
                'kind' => 'deposit',
                'amount' => $netAmount,
                'metadata' => [
                    'original_amount' => $amount,
                    'commission_percent' => $commissionPercent,
                    'commission_amount' => $commission,
                    'artisan_request_id' => $request->id,
                ],
            ]);

            $wallet->increment('available_balance', $netAmount);
        });
    }

    /**
     * Process an artisan payout (withdrawal).
     */
    public function processPayout(Artisan $artisan, Transaction $transaction): void
    {
        DB::transaction(function () use ($artisan, $transaction) {
            $wallet = $this->getWalletForArtisan($artisan);

            // Check idempotency
            $existingEntry = $wallet->entries()->where('transaction_id', $transaction->transaction_id)->first();
            if ($existingEntry) {
                return;
            }

            $wallet->entries()->create([
                'transaction_id' => $transaction->transaction_id,
                'kind' => 'payout',
                'amount' => -$transaction->amount,
                'metadata' => [
                    'provider' => $transaction->provider,
                ],
            ]);

            $wallet->decrement('available_balance', $transaction->amount);
        });
    }

    /**
     * Reserve funds on the artisan's wallet when a payout is accepted by PawaPay.
     * Idempotent based on the payout_reservation entry.
     */
    public function reservePayout(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $wallet = $this->walletForTransaction($transaction);
            $amount = (int) $transaction->amount;

            $existing = ArtisanWalletEntry::where('transaction_id', $transaction->transaction_id)
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

    /**
     * Settle an artisan payout once its final status is known.
     * COMPLETED: confirm the debit. FAILED/REJECTED/CANCELLED: release the reservation.
     */
    public function settle(Transaction $transaction, TransactionStatus $status): void
    {
        DB::transaction(function () use ($transaction, $status): void {
            $transaction->refresh();
            $transaction->update(['status' => $status]);

            if ($transaction->type !== TransactionType::PAYOUT) {
                return;
            }

            $wallet = $this->walletForTransaction($transaction);
            $reservation = ArtisanWalletEntry::where('transaction_id', $transaction->transaction_id)
                ->where('kind', 'payout_reservation')
                ->first();

            if ($status === TransactionStatus::COMPLETED
                && ! ArtisanWalletEntry::where('transaction_id', $transaction->transaction_id)->where('kind', 'payout_debit')->exists()) {
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
                    'metadata' => [
                        'provider' => $transaction->provider,
                    ],
                ]);
            }

            if (in_array($status, [TransactionStatus::FAILED, TransactionStatus::REJECTED, TransactionStatus::CANCELLED], true)
                && $reservation
                && ! ArtisanWalletEntry::where('transaction_id', $transaction->transaction_id)->where('kind', 'payout_release')->exists()) {
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

    /**
     * Whether the given transaction has a payout reserved on an artisan wallet.
     */
    public function hasPayoutReservation(Transaction $transaction): bool
    {
        return ArtisanWalletEntry::where('transaction_id', $transaction->transaction_id)
            ->where('kind', 'payout_reservation')
            ->exists();
    }

    private function walletForTransaction(Transaction $transaction): ArtisanWallet
    {
        $artisan = Artisan::where('user_id', $transaction->user_id)->firstOrFail();

        return $this->getWalletForArtisan($artisan);
    }
}
