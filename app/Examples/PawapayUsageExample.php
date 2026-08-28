<?php

namespace App\Examples;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\DataTransferObjects\Pawapay\RefundRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Support\Str;

/**
 * Example usage of PawaPay integration.
 *
 * This class demonstrates how to use the PawaPay service in your application.
 * DO NOT use this code directly in production - these are examples only.
 */
class PawapayUsageExample
{
    public function __construct(
        protected PawapayService $pawapay
    ) {}

    /**
     * Example: Collect a payment from a customer (deposit).
     */
    public function collectPayment(int $userId, int $amount, string $phoneNumber): Transaction
    {
        // 1. Generate UUIDs BEFORE calling the API (idempotency)
        $depositId = Str::uuid()->toString();
        $transactionId = Str::uuid()->toString();

        // 2. Create transaction record in PENDING status
        $transaction = Transaction::create([
            'transaction_id' => $transactionId,
            'user_id' => $userId,
            'deposit_id' => $depositId,
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::PENDING,
            'amount' => $amount, // In cents/minor units
            'currency' => config('pawapay.default_currency', 'CDF'),
            'provider' => null, // Will be set after provider prediction
        ]);

        // 3. Predict provider from phone number
        try {
            $prediction = $this->pawapay->predictProvider($phoneNumber);
            $provider = $prediction['provider'];
            $normalizedPhone = $prediction['phoneNumber'];

            $transaction->update(['provider' => $provider]);
        } catch (PawaPayException $e) {
            // Failed to predict provider - handle error
            $transaction->update([
                'status' => TransactionStatus::FAILED,
                'raw_response' => ['error' => $e->getMessage()],
            ]);

                $transaction->update([
                    'status' => TransactionStatus::REJECTED,
                    'raw_response' => $response,
                ]);
            } elseif ($response['status'] === 'DUPLICATE_IGNORED') {
                // Duplicate deposit - safe to retry
                $transaction->update([
                    'status' => TransactionStatus::DUPLICATE_IGNORED,
                    'raw_response' => $response,
                ]);
            }

            return $transaction;
        } catch (PawaPayException $e) {
            // Network error or API issue
            // DO NOT mark as FAILED - status remains PENDING until confirmed
            throw $e;
        }
    }

    /**
     * Example: Send money to multiple customers at once (bulk payout).
     *
     * @param  array<array{user_id: int, amount: int, phone_number: string}>  $recipients
     * @return array<Transaction>
     */
    public function sendBulkPayout(array $recipients): array
    {
        $payouts = [];
        $transactions = [];

        foreach ($recipients as $recipient) {
            $payoutId = Str::uuid()->toString();

            // Create transaction
            $transaction = Transaction::create([
                'transaction_id' => Str::uuid()->toString(),
                'user_id' => $recipient['user_id'],
                'payout_id' => $payoutId,
                'type' => TransactionType::PAYOUT,
                'status' => TransactionStatus::PENDING,
                'amount' => $recipient['amount'],
                'currency' => config('pawapay.default_currency'),
            ]);

            $transactions[] = $transaction;

            // Prepare payout request
            $prediction = $this->pawapay->predictProvider($recipient['phone_number']);

            $payouts[] = new PayoutRequest(
                payoutId: $payoutId,
                phoneNumber: $prediction['phoneNumber'],
                provider: $prediction['provider'],
                amount: (string) ($recipient['amount'] / 100),
                currency: config('pawapay.default_currency'),
            );
        }

        // Send bulk payout
        $responses = $this->pawapay->initiateBulkPayout($payouts);

        // Update transactions with responses
        foreach ($responses as $index => $response) {
            if (isset($transactions[$index])) {
                $status = TransactionStatus::tryFrom($response['status'] ?? '') ?? TransactionStatus::PENDING;

                $transactions[$index]->update([
                    'status' => $status,
                    'raw_response' => $response,
                ]);
            }
        }

        return $transactions;
    }

    /**
     * Example: Send money to a customer (payout).
     */
    public function sendPayout(int $userId, int $amount, string $phoneNumber): Transaction
    {
        $payoutId = Str::uuid()->toString();

        $transaction = Transaction::create([
            'transaction_id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'payout_id' => $payoutId,
            'type' => TransactionType::PAYOUT,
            'status' => TransactionStatus::PENDING,
            'amount' => $amount,
            'currency' => config('pawapay.default_currency'),
        ]);

        // Predict provider
        $prediction = $this->pawapay->predictProvider($phoneNumber);

        $request = new PayoutRequest(
            payoutId: $payoutId,
            phoneNumber: $prediction['phoneNumber'],
            provider: $prediction['provider'],
            amount: (string) ($amount / 100),
            currency: config('pawapay.default_currency'),
        );

        $response = $this->pawapay->initiatePayout($request);

        if ($response['status'] === 'ACCEPTED') {
            $transaction->update([
                'status' => TransactionStatus::ACCEPTED,
                'provider' => $prediction['provider'],
                'raw_response' => $response,
            ]);
        } elseif ($response['status'] === 'ENQUEUED') {
            $transaction->update([
                'status' => TransactionStatus::ENQUEUED,
                'provider' => $prediction['provider'],
                'raw_response' => $response,
            ]);
        }

        return $transaction;
    }

    /**
     * Example: Refund a completed deposit.
     */
    public function refundPayment(string $originalDepositId): Transaction
    {
        // 1. Find the original deposit
        $originalTransaction = Transaction::where('deposit_id', $originalDepositId)
            ->where('status', TransactionStatus::COMPLETED)
            ->firstOrFail();

        // 2. Create refund transaction
        $refundId = Str::uuid()->toString();

        $transaction = Transaction::create([
            'transaction_id' => Str::uuid()->toString(),
            'user_id' => $originalTransaction->user_id,
            'refund_id' => $refundId,
            'type' => TransactionType::REFUND,
            'status' => TransactionStatus::PENDING,
            'amount' => $originalTransaction->amount,
            'currency' => $originalTransaction->currency,
            'provider' => $originalTransaction->provider,
        ]);

        // 3. Initiate refund
        $request = new RefundRequest(
            refundId: $refundId,
            depositId: $originalDepositId,
            // amount: null means full refund
        );

        $response = $this->pawapay->initiateRefund($request);

        if ($response['status'] === 'ACCEPTED') {
            $transaction->update([
                'status' => TransactionStatus::ACCEPTED,
                'raw_response' => $response,
            ]);
        }

        return $transaction;
    }

    /**
     * Example: Create a hosted payment page.
     */
    public function createHostedPaymentPage(int $userId, int $amount): string
    {
        $depositId = Str::uuid()->toString();

        // Create transaction
        $transaction = Transaction::create([
            'transaction_id' => Str::uuid()->toString(),
            'user_id' => $userId,
            'deposit_id' => $depositId,
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::PENDING,
            'amount' => $amount,
            'currency' => config('pawapay.default_currency'),
        ]);

        // Create payment page
        $response = $this->pawapay->createPaymentPage(
            depositId: $depositId,
            returnUrl: route('payments.return', ['transaction' => $transaction->transaction_id]),
            amount: (string) ($amount / 100),
            currency: config('pawapay.default_currency'),
            clientReferenceId: "TXN-{$transaction->transaction_id}",
        );

        // Return redirect URL
        return $response['redirectUrl'];
    }

    /**
     * Example: Check transaction status manually.
     */
    public function checkTransactionStatus(Transaction $transaction): TransactionStatus
    {
        if ($transaction->type === TransactionType::DEPOSIT && $transaction->deposit_id) {
            $response = $this->pawapay->getDepositStatus($transaction->deposit_id);
        } elseif ($transaction->type === TransactionType::PAYOUT && $transaction->payout_id) {
            $response = $this->pawapay->getPayoutStatus($transaction->payout_id);
        } elseif ($transaction->type === TransactionType::REFUND && $transaction->refund_id) {
            $response = $this->pawapay->getRefundStatus($transaction->refund_id);
        } else {
            throw new \InvalidArgumentException('Transaction missing PawaPay ID');
        }

        if ($response['status'] === 'FOUND' && isset($response['data']['status'])) {
            $newStatus = TransactionStatus::tryFrom($response['data']['status']);

            if ($newStatus && $newStatus !== $transaction->status) {
                $transaction->update([
                    'status' => $newStatus,
                    'raw_response' => array_merge($transaction->raw_response ?? [], $response['data']),
                ]);
            }

            return $newStatus ?? $transaction->status;
        }

        return $transaction->status;
    }

    /**
     * Example: Get available providers with branding.
     */
    public function getAvailableProviders(): array
    {
        try {
            $config = $this->pawapay->getActiveConfiguration();

            $providers = [];
            foreach ($config['correspondents'] ?? [] as $correspondent) {
                $code = $correspondent['correspondent'];
                $providers[$code] = [
                    'code' => $code,
                    'name' => $correspondent['displayName'] ?? $code,
                    'currencies' => $correspondent['currencies'] ?? [],
                    'limits' => [
                        'min' => $correspondent['minAmount'] ?? null,
                        'max' => $correspondent['maxAmount'] ?? null,
                    ],
                ];
            }

            return $providers;
        } catch (PawaPayException $e) {
            // Fallback to static config
            return config('pawapay.providers', []);
        }
    }

    /**
     * Example: Check if a provider is available right now.
     */
    public function isProviderAvailable(string $provider): bool
    {
        try {
            $availability = $this->pawapay->getAvailability();

            foreach ($availability['correspondents'] ?? [] as $correspondent) {
                if ($correspondent['correspondent'] === $provider) {
                    return $correspondent['available'] ?? false;
                }
            }

            return false;
        } catch (PawaPayException $e) {
            // Assume available if we can't check
            return true;
        }
    }
}
