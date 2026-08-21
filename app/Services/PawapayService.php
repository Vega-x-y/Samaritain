<?php

namespace App\Services;

use App\Exceptions\PawaPayException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for interacting with the pawaPay mobile money API.
 *
 * pawaPay is an asynchronous payment gateway: you initiate a deposit/payout,
 * get an ACCEPTED/REJECTED response immediately, then the final status
 * (COMPLETED/FAILED) arrives later via a callback.
 *
 * Key rules:
 *  - depositId/payoutId must be a UUIDv4 generated and persisted BEFORE calling the API.
 *  - Never mark a payment FAILED just because the HTTP call errored or timed out.
 *    Only trust NOT_FOUND from a status-check call for that.
 *  - Amounts are strings, not floats.
 */
class PawapayService
{
    protected string $baseUrl;

    protected string $token;

    protected ?string $callbackSecret;

    protected bool $verifyCallbackSignature;

    public function __construct()
    {
        $this->baseUrl = config('services.pawapay.base_url');
        $this->token = config('services.pawapay.token');
        $this->callbackSecret = config('services.pawapay.callback_secret');
        $this->verifyCallbackSignature = (bool) config('services.pawapay.verify_callback_signature', false);
    }

    /**
     * Build a base HTTP request with authentication and JSON headers.
     */
    protected function httpClient()
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Predict the mobile money provider for a given MSISDN.
     *
     * Always use this endpoint to normalize phone numbers and detect the
     * provider — never hand-roll phone validation.
     *
     * @param  string  $msisdn  The phone number in E.164 format.
     * @return array{provider?: string|null, phoneNumber?: string, country?: string|null, raw: array<string, mixed>}
     *
     * @throws PawaPayException
     */
    public function predictProvider(string $msisdn): array
    {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/predict-provider", [
                'phoneNumber' => $msisdn,
            ]);

        if ($response->failed()) {
            Log::warning('pawaPay predict-provider failed', [
                'msisdn' => $msisdn,
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

        // pawaPay returns { country, provider, phoneNumber }. Normalize to a
        // stable shape so callers never have to guess the response field names.
        return [
            'provider' => $body['provider'] ?? null,
            'phoneNumber' => $body['phoneNumber'] ?? $msisdn,
            'country' => $body['country'] ?? null,
            'raw' => $body,
        ];
    }

    /**
     * Retrieve active configuration (supported providers, currency, decimals, etc.).
     *
     * Check decimalsInAmount (NONE vs TWO_PLACES) before rounding/formatting amounts.
     *
     * @return array<string, mixed>
     *
     * @throws PawaPayException
     */
    public function getActiveConfiguration(): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/active-conf");

        if ($response->failed()) {
            Log::warning('pawaPay active-configuration request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Impossible de récupérer la configuration pawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json();
    }

    /**
     * Create a deposit (collect money from a customer).
     *
     * The depositId must be a UUIDv4 generated and persisted by your application
     * BEFORE calling this method. Reusing a depositId returns DUPLICATE_IGNORED.
     *
     * @param  string  $depositId  The UUIDv4 idempotency key.
     * @param  array  $data  The deposit payload (payer, amountDetails, provider, etc.).
     * @return array The pawaPay response containing status, provider, etc.
     *
     * @throws PawaPayException
     */
    public function createDeposit(string $depositId, array $data): array
    {
        $payload = array_merge($data, ['depositId' => $depositId]);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/deposits", $payload);

        if ($response->failed()) {
            Log::warning('pawaPay deposit creation failed', [
                'depositId' => $depositId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de la création du dépôt pawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json();
    }

    /**
     * Create a hosted payment page for a deposit.
     *
     * The payment is only initiated after the customer completes the hosted
     * page. The depositId remains the idempotency anchor for callbacks and
     * reconciliation.
     *
     * @param  string  $depositId  The UUIDv4 idempotency key.
     * @param  string  $returnUrl  The server-side return URL.
     * @param  int  $amount  The amount to collect.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $clientReferenceId  Your internal reference.
     * @return array<string, mixed>
     *
     * @throws PawaPayException
     */
    public function createPaymentPage(
        string $depositId,
        string $returnUrl,
        int $amount,
        string $currency,
        string $clientReferenceId
    ): array {
        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/paymentpage", [
                'depositId' => $depositId,
                'returnUrl' => $returnUrl,
                'amount' => (string) $amount,
                'currency' => $currency,
                'clientReferenceId' => $clientReferenceId,
            ]);

        if ($response->failed()) {
            Log::warning('pawaPay payment page creation failed', [
                'depositId' => $depositId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de la création de la page de paiement pawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        $result = $response->json() ?? [];

        if (empty($result['redirectUrl'])) {
            throw new PawaPayException(
                'pawaPay n’a pas fourni de lien de paiement.',
                $response->status(),
                $response->body(),
            );
        }

        return $result;
    }

    /**
     * List the Mobile Money providers the merchant can offer in-app.
     *
     * On a dedicated step, the customer picks one of these operators (MTN or
     * Airtel) and enters their number, then a direct deposit (USSD push) is
     * initiated — the hosted payment page is intentionally not used.
     *
     * @return array<string, string> Provider code => display label.
     */
    public function availableProviders(): array
    {
        return (array) config('services.pawapay.providers', [
            'MTN_MOMO_COG' => 'MTN Mobile Money',
            'AIRTEL_COG' => 'Airtel Money',
        ]);
    }

    /**
     * Fetch provider branding (display name + logo) from the pawaPay toolkit so
     * the in-app payment step can show the operator's real logo.
     *
     * Returns an empty array when the toolkit is unreachable.
     *
     * @return array<string, array{displayName?: string, logo?: string|null, status?: string|null}>
     */
    public function getProviderDetails(): array
    {
        try {
            $config = $this->getActiveConfiguration();
        } catch (PawaPayException $e) {
            Log::warning('pawaPay active-configuration unavailable for branding', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        return $this->normalizeProviderDetails($config);
    }

    /**
     * Normalize the active-configuration response into a map keyed by provider code.
     *
     * The toolkit returns per-provider arrays containing `logo` and `displayName`.
     * When a provider is just a string (no branding available) it is preserved.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, array{displayName?: string, logo?: string|null, status?: string|null}>
     */
    protected function normalizeProviderDetails(array $body): array
    {
        $out = [];

        $providers = $body['providers'] ?? [];

        if (! is_array($providers)) {
            return $out;
        }

        foreach ($providers as $entry) {
            if (is_string($entry)) {
                $out[$entry] = ['displayName' => $entry, 'logo' => null];

                continue;
            }

            if (! is_array($entry)) {
                continue;
            }

            $code = $entry['provider'] ?? $entry['providerId'] ?? $entry['code'] ?? null;

            if (! $code) {
                continue;
            }

            $out[$code] = [
                'displayName' => $entry['displayName'] ?? $code,
                'logo' => $entry['logo'] ?? null,
                'status' => $entry['status'] ?? null,
            ];
        }

        return $out;
    }

    /**
     * Build the list of providers with branding (label + logo) for the in-app
     * payment step, falling back to the configured labels when pawaPay cannot
     * be reached.
     *
     * @return array<string, array{label: string, logo: string|null, status: string|null}>
     */
    public function availableProvidersWithBranding(): array
    {
        $labels = $this->availableProviders();
        $details = $this->getProviderDetails();

        $out = [];

        foreach ($labels as $code => $label) {
            $detail = $details[$code] ?? [];

            $out[$code] = [
                'label' => $detail['displayName'] ?? $label,
                'logo' => $detail['logo'] ?? null,
                'status' => $detail['status'] ?? null,
            ];
        }

        return $out;
    }

    /**
     * Normalize a customer-entered phone number to the MSISDN format pawaPay
     * expects (digits only, country code required, no leading zero).
     *
     * Handles inputs like "06 800 71 38", "+242068007138" or "24268007138".
     *
     * @param  string  $phone  The raw phone number entered by the customer.
     * @param  string|null  $dialCode  ISO country prefix (default from config).
     * @return string The normalized MSISDN.
     *
     * @throws PawaPayException
     */
    public function normalizeMsisdn(string $phone, ?string $dialCode = null): string
    {
        $dialCode ??= (string) config('services.pawapay.dial_code', '242');

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            throw new PawaPayException('Numéro de téléphone invalide.');
        }

        // Remove the country prefix first, then any national leading zero, then
        // re-apply the country code. This makes every input format converge to
        // the same MSISDN (e.g. "+242 06..", "06.." and "24206.." -> "2426...").
        if (str_starts_with($digits, $dialCode)) {
            $digits = (string) substr($digits, strlen($dialCode));
        }

        $digits = ltrim($digits, '0');

        return $dialCode.$digits;
    }

    /**
     * Initiate a direct deposit (USSD push to the customer's phone) without using
     * the hosted payment page.
     *
     * The depositId must be a UUIDv4 generated and persisted by your application
     * BEFORE calling this method. Reusing a depositId returns DUPLICATE_IGNORED.
     *
     * @param  string  $depositId  The UUIDv4 idempotency key.
     * @param  string  $phoneNumber  The normalized payer MSISDN.
     * @param  string  $provider  A pawaPay provider code (see availableProviders()).
     * @param  int  $amount  The amount to collect.
     * @param  string  $currency  ISO 4217 currency code.
     * @param  string  $clientReferenceId  Your internal reference (facture, commande...).
     * @param  string  $customerMessage  Note shown to the customer by some operators.
     * @param  array<int, array<string, string>>  $metadata  Up to 10 single-key fields.
     * @return array The pawaPay response (status ACCEPTED/REJECTED/DUPLICATE_IGNORED, ...).
     *
     * @throws PawaPayException
     */
    public function initiateDeposit(
        string $depositId,
        string $phoneNumber,
        string $provider,
        int $amount,
        string $currency,
        string $clientReferenceId,
        string $customerMessage = 'Samaritain',
        array $metadata = []
    ): array {
        $payload = [
            'depositId' => $depositId,
            'amount' => (string) $amount,
            'currency' => $currency,
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $phoneNumber,
                    'provider' => $provider,
                ],
            ],
            'clientReferenceId' => $clientReferenceId,
            'customerMessage' => $customerMessage,
            'metadata' => $metadata,
        ];

        return $this->createDeposit($depositId, $payload);
    }

    /**
     * Check the status of a deposit.
     *
     * The final status (COMPLETED/FAILED) arrives via callback. This endpoint
     * is primarily used for reconciliation of payments stuck in
     * PENDING/PROCESSING. NOT_FOUND means the deposit was never created —
     * do NOT treat it as FAILED.
     *
     * @param  string  $depositId  The UUIDv4 deposit identifier.
     * @return array The pawaPay status response.
     *
     * @throws PawaPayException Only for HTTP-level failures; NOT_FOUND is returned as a status array.
     */
    public function getDepositStatus(string $depositId): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/deposits/{$depositId}");

        // NOT_FOUND is a legitimate result — the deposit may not have been created
        if ($response->status() === 404) {
            return [
                'depositId' => $depositId,
                'status' => 'NOT_FOUND',
                'reason' => 'Deposit not found',
            ];
        }

        if ($response->failed()) {
            Log::warning('pawaPay status check failed', [
                'depositId' => $depositId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de la vérification du statut du dépôt.',
                $response->status(),
                $response->body(),
            );
        }

        return $this->normalizeStatusResponse($depositId, 'depositId', $response->json() ?? []);
    }

    /**
     * pawaPay status-check endpoints return an envelope:
     *
     *     { "status": "FOUND"|"NOT_FOUND", "data": { "status": "COMPLETED"|..., ... } }
     *
     * The outer ``status`` only tells whether the resource exists; the final
     * transaction state lives in ``data.status``. This flattens the response so
     * callers can read the final status directly from the ``status`` key.
     *
     * @param  string  $id  The depositId / payoutId used for the lookup.
     * @param  string  $idField  "depositId" or "payoutId".
     * @param  array  $body  The raw pawaPay JSON body.
     * @return array<string, mixed>
     */
    protected function normalizeStatusResponse(string $id, string $idField, array $body): array
    {
        $status = strtoupper((string) ($body['status'] ?? 'UNKNOWN'));

        // The lookup succeeded; unwrap data.* to reach the final transaction status.
        if ($status === 'FOUND' && isset($body['data']) && is_array($body['data'])) {
            $data = $body['data'];
            $status = strtoupper((string) ($data['status'] ?? $status));
            $body = $data;
        }

        $body['status'] = $status;
        $body[$idField] ??= $id;

        return $body;
    }

    /**
     * Verify a pawaPay callback signature (HMAC-SHA256 of the request body).
     *
     * @param  string  $payload  The raw request body.
     * @param  string  $signature  The signature header value.
     * @return bool True if the signature is valid (or if no secret is configured).
     */
    public function verifyCallbackSignature(string $payload, string $signature): bool
    {
        // Signature verification is disabled unless explicitly enabled.
        if (! $this->verifyCallbackSignature) {
            return true;
        }

        if (! $this->callbackSecret) {
            Log::warning('pawaPay signature verification is enabled but no callback secret is configured — verification skipped.');

            return true;
        }

        $expected = hash_hmac('sha256', $payload, $this->callbackSecret);

        return hash_equals($expected, $signature);
    }

    /**
     * Initiate a payout (send money to a recipient via Mobile Money).
     *
     * The payoutId must be a UUIDv4 generated and persisted by your application
     * BEFORE calling this method. Reusing a payoutId returns DUPLICATE_IGNORED.
     *
     * @param  string  $payoutId  The UUIDv4 idempotency key.
     * @param  array  $data  The payout payload (recipient, amountDetails, provider, etc.)
     * @return array The pawaPay response containing status, provider, etc.
     *
     * @throws PawaPayException
     */
    public function createPayout(string $payoutId, array $data): array
    {
        $payload = array_merge($data, ['payoutId' => $payoutId]);

        $response = $this->httpClient()
            ->post("{$this->baseUrl}/v2/payouts", $payload);

        if ($response->failed()) {
            Log::warning('pawaPay payout creation failed', [
                'payoutId' => $payoutId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de la création du paiement sortant pawaPay.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json();
    }

    /**
     * Check the status of a payout.
     *
     * Used for reconciliation of payouts stuck in PENDING/PROCESSING.
     * NOT_FOUND means the payout was never created — do NOT treat as FAILED.
     *
     * @param  string  $payoutId  The UUIDv4 payout identifier.
     * @return array The pawaPay status response.
     *
     * @throws PawaPayException Only for HTTP-level failures; NOT_FOUND is returned as a status array.
     */
    public function getPayoutStatus(string $payoutId): array
    {
        $response = $this->httpClient()
            ->get("{$this->baseUrl}/v2/payouts/{$payoutId}");

        if ($response->status() === 404) {
            return [
                'payoutId' => $payoutId,
                'status' => 'NOT_FOUND',
                'reason' => 'Payout not found',
            ];
        }

        if ($response->failed()) {
            Log::warning('pawaPay payout status check failed', [
                'payoutId' => $payoutId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new PawaPayException(
                'Erreur lors de la vérification du statut du paiement sortant.',
                $response->status(),
                $response->body(),
            );
        }

        return $this->normalizeStatusResponse($payoutId, 'payoutId', $response->json() ?? []);
    }
}
