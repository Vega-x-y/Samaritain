<?php

namespace App\Services;

use App\Models\Artisan;
use App\Models\ArtisanWallet;
use App\Models\Transaction;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

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
            if (!$request || !$request->artisan) {
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
}
