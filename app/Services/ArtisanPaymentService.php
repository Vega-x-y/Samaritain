<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ArtisanPaymentService
{
    public function __construct(
        protected ArtisanWalletService $walletService
    ) {}

    /**
     * Handle successful PawaPay deposit for an artisan request.
     * Called when the transaction status becomes COMPLETED.
     */
    public function handleSuccessfulDownPayment(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $request = $transaction->artisanRequest;
            
            if (!$request) {
                return;
            }

            // Update the request payment status if not already paid
            if ($request->payment_status !== 'DOWN_PAYMENT_PAID' && $request->payment_status !== 'FULLY_PAID') {
                $request->update([
                    'payment_status' => 'DOWN_PAYMENT_PAID'
                ]);
            }

            // Credit the wallet
            $this->walletService->creditDownPayment($transaction);
        });
    }
}
