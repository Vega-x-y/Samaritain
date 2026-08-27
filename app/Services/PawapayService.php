<?php

namespace App\Services;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\DataTransferObjects\Pawapay\RefundRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for interacting with the PawaPay mobile money API (v2).
 *
 * PawaPay is an asynchronous payment gateway: you initiate a deposit/payout,
 * get an ACCEPTED/REJECTED response immediately, then the final status
 * (COMPLETED/FAILED) arrives later via a callback or polling.
 *
 * Key rules:
 *  - depositId/payoutId/refundId must be a UUIDv4 generated and persisted BEFORE calling the API.
 *  - Always check 'status' in the response, even on HTTP 200 (can be ACCEPTED or REJECTED).
 *  - Never mark a payment FAILED just because the HTTP call errored or timed out.
 *    Only trust the status from a callback or GET status endpoint.
 *  - Amounts are strings, not floats or integers.
 *  - Phone numbers: digits only, no +, no spaces, country code required, no leading zero.
 *
 * @see https://docs.pawapay.io/v2/docs/welcome
 */
class PawapayService
{
    protected string $baseUrl;

    protected ?string $token;

    protected bool $verifyCallbackSignature;

    protected int $timeout;

    protected int $retryTimes;

    public function __construct()
    {
        $this->baseUrl = config('pawapay.base_url');
        $this->token = config('pawapay.token');
        $this->verifyCallbackSignature = (bool) config('pawapay.verify_callback_signature', false);
        $this->timeout = (int) config('pawapay.timeout', 30);
        $this->retryTimes = (int) config('pawapay.retry_times', 2);
    }

    /**
     * Build a base HTTP request with authentication and JSON headers.
     */
    protected function httpClient()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->timeout($this->timeout)
            ->connectTimeout(5)
            ->retry($this->retryTimes, 100, throw: false);
    }

    /**
     * Initiate a deposit (collect payment from customer).
     *
     * @throws PawaPayException
     */
    public function initiateDeposit(DepositRequest $request): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/deposits", $request->toArray());

        if ($response->failed()) {
            Log::warning('PawaPay deposit initiation failed', [
                'depositId' => $request->depositId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de l\'initiation du dépôt PawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        $data = $response->json() ?? [];

        // Even on HTTP 200, check the status field
        if (isset($data['status']) && $data['status'] === 'REJECTED') {
            Log::warning('PawaPay deposit rejected', [
                'depositId' => $request->depositId,
                'failureReason' => $data['failureReason'] ?? null,
            ]);
        }

        return $data;
    }

    /**
     * Get the status of a deposit.
     *
     * @return array{status: string, data: array|null}
     *
     * @throws PawaPayException
     */
    public function getDepositStatus(string $depositId): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/deposits/{$depositId}");

        if ($response->failed()) {
            Log::warning('PawaPay deposit status check failed', [
                'depositId' => $depositId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible de récupérer le statut du dépôt.',
                $response->status(),
                $response->body(),
            );
        }

        return $this->normalizeStatusResponse($response->json() ?? []);
    }

    /**
     * Initiate a payout (send money to customer).
     *
     * @throws PawaPayException
     */
    public function initiatePayout(PayoutRequest $request): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/payouts", $request->toArray());

        if ($response->failed()) {
            Log::warning('PawaPay payout initiation failed', [
                'payoutId' => $request->payoutId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de l\'initiation du retrait PawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        $data = $response->json() ?? [];

        if (isset($data['status']) && $data['status'] === 'REJECTED') {
            Log::warning('PawaPay payout rejected', [
                'payoutId' => $request->payoutId,
                'failureReason' => $data['failureReason'] ?? null,
            ]);
        }

        return $data;
    }

    /**
     * Get the status of a payout.
     *
     * @return array{status: string, data: array|null}
     *
     * @throws PawaPayException
     */
    public function getPayoutStatus(string $payoutId): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/payouts/{$payoutId}");

        if ($response->failed()) {
            Log::warning('PawaPay payout status check failed', [
                'payoutId' => $payoutId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible de récupérer le statut du retrait.',
                $response->status(),
                $response->body(),
            );
        }

        return $this->normalizeStatusResponse($response->json() ?? []);
    }

    /**
     * Initiate bulk payouts (send money to multiple customers).
     *
     * @param  PayoutRequest[]  $payouts  Array of payout requests
     * @return array Response with accepted/rejected payouts
     *
     * @throws PawaPayException
     */
    public function initiateBulkPayout(array $payouts): array
    {
        $payload = array_map(fn (PayoutRequest $payout) => $payout->toArray(), $payouts);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/payouts/bulk", $payload);

        if ($response->failed()) {
            Log::warning('PawaPay bulk payout initiation failed', [
                'count' => count($payouts),
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de l\'initiation des retraits groupés PawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Cancel an enqueued payout.
     *
     * Only works if the payout is still ENQUEUED and hasn't been processed yet.
     *
     * @throws PawaPayException
     */
    public function cancelPayout(string $payoutId): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/payouts/{$payoutId}/cancel");

        if ($response->failed()) {
            Log::warning('PawaPay payout cancellation failed', [
                'payoutId' => $payoutId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible d\'annuler le retrait.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Initiate a refund for a completed deposit.
     *
     * @throws PawaPayException
     */
    public function initiateRefund(RefundRequest $request): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/refunds", $request->toArray());

        if ($response->failed()) {
            Log::warning('PawaPay refund initiation failed', [
                'refundId' => $request->refundId,
                'depositId' => $request->depositId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de l\'initiation du remboursement PawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        $data = $response->json() ?? [];

        if (isset($data['status']) && $data['status'] === 'REJECTED') {
            Log::warning('PawaPay refund rejected', [
                'refundId' => $request->refundId,
                'depositId' => $request->depositId,
                'failureReason' => $data['failureReason'] ?? null,
            ]);
        }

        return $data;
    }

    /**
     * Get the status of a refund.
     *
     * @return array{status: string, data: array|null}
     *
     * @throws PawaPayException
     */
    public function getRefundStatus(string $refundId): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/refunds/{$refundId}");

        if ($response->failed()) {
            Log::warning('PawaPay refund status check failed', [
                'refundId' => $refundId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible de récupérer le statut du remboursement.',
                $response->status(),
                $response->body(),
            );
        }

        return $this->normalizeStatusResponse($response->json() ?? []);
    }

    /**
     * Normalize a deposit/payout/refund status response.
     *
     * pawaPay wraps the actual status of a resource in a FOUND envelope:
     *
     *     { "status": "FOUND", "data": { "status": "COMPLETED", ... } }
     *
     * Consumers expect the real status (e.g. COMPLETED/FAILED) directly on the
     * "status" key, so we unwrap the envelope when present.
     *
     * @param  array  $response  Raw response from the status-check endpoint
     * @return array Normalized response with the real status on the "status" key
     */
    protected function normalizeStatusResponse(array $response): array
    {
        $envelopeStatus = strtoupper((string) ($response['status'] ?? ''));
        $innerStatus = $response['data']['status'] ?? null;

        if ($envelopeStatus === 'FOUND' && is_string($innerStatus) && $innerStatus !== '') {
            $response['status'] = $innerStatus;
        }

        return $response;
    }

    /**
     * Create a hosted payment page for a deposit.
     *
     * The payment is only initiated after the customer completes the hosted page.
     * Always verify final status via callback or GET status endpoint.
     *
     * @param  string  $depositId  UUIDv4 idempotency key
     * @param  string  $returnUrl  URL to redirect after payment
     * @param  string  $amount  Amount as string
     * @param  string  $currency  ISO 4217 currency code
     * @param  string|null  $clientReferenceId  Your internal reference
     * @param  string|null  $country  Optional ISO 3166-1 alpha-3 country filter
     * @param  string|null  $language  Optional payment page language (EN|FR)
     * @param  string|null  $reason  Optional text shown to the customer
     * @param  string|null  $customerMessage  Optional 4-22 char narration
     * @param  array|null  $metadata  Optional metadata (up to 10 fields)
     * @param  string|null  $phoneNumber  Optional pre-filled MSISDN
     * @return array{redirectUrl: string, ...}
     *
     * @throws PawaPayException
     */
    public function createPaymentPage(
        string $depositId,
        string $returnUrl,
        string $amount,
        string $currency,
        ?string $clientReferenceId = null,
        ?string $country = null,
        ?string $language = null,
        ?string $reason = null,
        ?string $customerMessage = null,
        ?array $metadata = null,
        ?string $phoneNumber = null
    ): array {
        $payload = [
            'depositId' => $depositId,
            'returnUrl' => $returnUrl,
            'amountDetails' => [
                'amount' => $amount,
                'currency' => $currency,
            ],
        ];

        if ($clientReferenceId !== null) {
            $payload['clientReferenceId'] = $clientReferenceId;
        }

        if ($country !== null) {
            $payload['country'] = $country;
        }

        if ($language !== null) {
            $payload['language'] = $language;
        }

        if ($reason !== null) {
            $payload['reason'] = $reason;
        }

        if ($customerMessage !== null) {
            $payload['customerMessage'] = $customerMessage;
        }

        if ($metadata !== null && count($metadata) > 0) {
            $payload['metadata'] = $metadata;
        }

        if ($phoneNumber !== null) {
            $payload['phoneNumber'] = $phoneNumber;
        }

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/paymentpage", $payload);

        if ($response->failed()) {
            Log::warning('PawaPay payment page creation failed', [
                'depositId' => $depositId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de la création de la page de paiement PawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        $result = $response->json() ?? [];

        if (empty($result['redirectUrl'])) {
            throw new PawaPayException(
                'PawaPay n\'a pas fourni de lien de redirection.',
                $response->status(),
                $response->body(),
            );
        }

        return $result;
    }

    /**
     * Resend the callback for a deposit.
     *
     * Useful if a callback was missed or needs to be reprocessed.
     *
     * @throws PawaPayException
     */
    public function resendDepositCallback(string $depositId): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/deposits/{$depositId}/resend-callback");

        if ($response->failed()) {
            throw new PawaPayException(
                'Impossible de renvoyer le callback du dépôt.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Resend the callback for a payout.
     *
     * @throws PawaPayException
     */
    public function resendPayoutCallback(string $payoutId): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/payouts/{$payoutId}/resend-callback");

        if ($response->failed()) {
            throw new PawaPayException(
                'Impossible de renvoyer le callback du retrait.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Resend the callback for a refund.
     *
     * @throws PawaPayException
     */
    public function resendRefundCallback(string $refundId): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/refunds/{$refundId}/resend-callback");

        if ($response->failed()) {
            throw new PawaPayException(
                'Impossible de renvoyer le callback du remboursement.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Predict the mobile money provider for a given phone number.
     *
     * Always use this endpoint to normalize phone numbers and detect the
     * provider — never hand-roll phone validation.
     *
     * @param  string  $phoneNumber  Phone number (with country code, no + or spaces)
     * @return array{provider: string|null, phoneNumber: string, country: string|null}
     *
     * @throws PawaPayException
     */
    public function predictProvider(string $phoneNumber): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/toolkit/predict-provider", [
                'phoneNumber' => $phoneNumber,
            ]);

        if ($response->failed()) {
            Log::warning('PawaPay predict-provider failed', [
                'phoneNumber' => $phoneNumber,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible de prédire le fournisseur pour ce numéro.',
                $response->status(),
                $response->body(),
            );
        }

        $body = $response->json() ?? [];

        return [
            'provider' => $body['provider'] ?? null,
            'phoneNumber' => $body['phoneNumber'] ?? $phoneNumber,
            'country' => $body['country'] ?? null,
        ];
    }

    /**
     * Get active configuration (supported providers, currencies, limits).
     *
     * This is the source of truth for which providers are actually
     * configured on your PawaPay account.
     *
     * @return array<string, mixed>
     *
     * @throws PawaPayException
     */
    public function getActiveConfiguration(): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/toolkit/active-configuration");

        if ($response->failed()) {
            Log::warning('PawaPay active-configuration request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible de récupérer la configuration PawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Get real-time availability status for providers.
     *
     * Use this to check if a provider is down or in maintenance.
     *
     * @return array<string, mixed>
     *
     * @throws PawaPayException
     */
    public function getAvailability(): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/toolkit/availability");

        if ($response->failed()) {
            Log::warning('PawaPay availability request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible de récupérer la disponibilité des fournisseurs.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    /**
     * List the Mobile Money providers configured in this application.
     *
     * This is a static list from config/pawapay.php.
     * For the real-time list, use getActiveConfiguration().
     *
     * @return array<string, string> Provider code => display label
     */
    public function availableProviders(): array
    {
        return (array) config('pawapay.providers', []);
    }

    /**
     * Normalize a phone number to PawaPay format (digits only, no + or spaces).
     *
     * @param  string  $phoneNumber  Raw phone number
     * @return string Normalized phone number
     */
    public function normalizePhoneNumber(string $phoneNumber): string
    {
        // Remove all non-digit characters
        $normalized = preg_replace('/\D/', '', $phoneNumber);

        // Remove leading + if present
        $normalized = ltrim($normalized, '+');

        // Remove leading 0 if present (country code should already be there)
        // But be careful: some numbers legitimately start with 0 after country code
        // For safety, only remove leading 0 if the number is long enough
        if (Str::startsWith($normalized, '0') && strlen($normalized) > 10) {
            $normalized = substr($normalized, 1);
        }

        return $normalized;
    }

    /**
     * Verify an incoming pawaPay server-to-server callback (RFC-9421 HTTP Message
     * Signatures).
     *
     * When signature verification is disabled (default) the callback is
     * accepted without verification. When enabled but no public key is
     * configured, the callback is rejected rather than silently accepted.
     *
     * @param  string  $payload  Raw request body
     * @param  array  $headers  Relevant signature headers
     * @param  string  $method  HTTP method
     * @param  string  $authority  Request host
     * @param  string  $path  Request path
     */
    public function verifyCallbackRequest(
        string $payload,
        array $headers,
        string $method,
        string $authority,
        string $path
    ): bool {
        if (! $this->verifyCallbackSignature) {
            return true;
        }

        Log::warning('pawaPay callback signature verification is enabled but not implemented; rejecting callback', [
            'authority' => $authority,
            'path' => $path,
        ]);

        return false;
    }

    /**
     * Handle an incoming callback from PawaPay.
     *
     * Automatically determines the transaction type (deposit, payout, refund)
     * and updates the corresponding Transaction model.
     *
     * @param  array  $payload  The callback payload from PawaPay
     */
    public function handleCallback(array $payload): ?Transaction
    {
        // Determine transaction type based on which ID is present
        $transactionId = $payload['depositId'] ?? $payload['payoutId'] ?? $payload['refundId'] ?? null;
        $type = isset($payload['depositId'])
            ? TransactionType::DEPOSIT
            : (isset($payload['payoutId']) ? TransactionType::PAYOUT : TransactionType::REFUND);

        if (! $transactionId) {
            Log::warning('PawaPay callback missing transaction ID', ['payload' => $payload]);

            return null;
        }

        // Find the transaction (by deposit_id, payout_id, refund_id, or transaction_id)
        $transaction = Transaction::where('deposit_id', $transactionId)
            ->orWhere('payout_id', $transactionId)
            ->orWhere('refund_id', $transactionId)
            ->orWhere('transaction_id', $transactionId)
            ->first();

        if (! $transaction) {
            Log::warning('PawaPay callback for unknown transaction', [
                'transactionId' => $transactionId,
                'type' => $type->value,
            ]);

            return null;
        }

        // Update transaction status
        $status = TransactionStatus::tryFrom($payload['status'] ?? '') ?? TransactionStatus::PENDING;

        // Only update if the new status is different (idempotency)
        if ($transaction->status?->value !== $status->value) {
            $transaction->update([
                'status' => $status->value,
                'raw_response' => array_merge($transaction->raw_response ?? [], $payload),
            ]);

            Log::info('PawaPay callback processed', [
                'transactionId' => $transactionId,
                'oldStatus' => $transaction->status,
                'newStatus' => $status->value,
            ]);
        }

        return $transaction;
    }
}
