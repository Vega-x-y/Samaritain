<?php

namespace App\Services;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\Exceptions\PawaPayException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PawapayService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = rtrim((string) config('services.pawapay.api_url'), '/');
    }

    public function initiateDeposit(DepositRequest $request): array
    {
        return $this->post('/deposits', $request->toArray());
    }

    public function initiatePayout(PayoutRequest $request): array
    {
        return $this->post('/payouts', $request->toArray());
    }

    public function getDepositStatus(string $depositId): array
    {
        return $this->get('/deposits/'.rawurlencode($depositId));
    }

    public function getPayoutStatus(string $payoutId): array
    {
        return $this->get('/payouts/'.rawurlencode($payoutId));
    }

    public function getActiveConfiguration(?string $country = null, string $operationType = 'DEPOSIT'): array
    {
        $query = array_filter([
            'country' => $country ?? config('services.pawapay.country', 'COG'),
            'operationType' => $operationType,
        ]);

        return $this->get('/active-conf', $query);
    }

    /** @return array<string, string> */
    public function activeProviders(string $operationType = 'DEPOSIT'): array
    {
        $configuration = $this->getActiveConfiguration(null, $operationType);
        $country = (string) config('services.pawapay.country', 'COG');
        $providers = [];

        foreach ((array) ($configuration['countries'] ?? []) as $countryConfiguration) {
            if (($countryConfiguration['country'] ?? null) !== $country) {

                continue;
            }
            foreach ((array) ($countryConfiguration['providers'] ?? []) as $provider) {
                $operation = $this->operationConfiguration($provider, $operationType);

                if (! $operation || ($operation['status'] ?? null) !== 'OPERATIONAL') {
                    continue;
                }

                $code = $provider['provider'] ?? null;
                $label = $provider['displayName'] ?? $code;

                if (is_string($code) && $code !== '') {
                    $providers[$code] = is_string($label) && $label !== '' ? $label : $code;
                }
            }
        }

        return $providers;
    }

    /**
     * Build the branding payload (logos, display names) for the providers of a
     * country configuration, keeping only providers operational for the given
     * operation type and the configured currency.
     *
     * @param  array<string, mixed>  $countryConfiguration
     * @return array<int, array{provider: string, displayName: string, logo: ?string}>
     */
    public function providerBranding(array $countryConfiguration, string $operationType = 'DEPOSIT'): array
    {
        $providers = [];

        foreach ((array) ($countryConfiguration['providers'] ?? []) as $provider) {
            $operation = $this->operationConfiguration($provider, $operationType);

            if (! $operation || ($operation['status'] ?? null) !== 'OPERATIONAL') {
                continue;
            }

            $code = $provider['provider'] ?? null;

            if (! is_string($code) || $code === '') {
                continue;
            }

            $label = $provider['displayName'] ?? $code;

            $providers[] = [
                'provider' => $code,
                'displayName' => is_string($label) && $label !== '' ? $label : $code,
                'logo' => isset($provider['logo']) && is_string($provider['logo']) ? $provider['logo'] : null,
            ];
        }

        return $providers;
    }

    /** @return array<string, mixed>|null */
    private function operationConfiguration(array $provider, string $operationType): ?array
    {
        foreach ((array) ($provider['currencies'] ?? []) as $currency) {
            if (($currency['currency'] ?? null) !== config('services.pawapay.currency', 'XAF')) {
                continue;
            }

            foreach ((array) ($currency['operationTypes'] ?? []) as $operation) {
                $configuration = $operation[$operationType] ?? null;

                if (is_array($configuration)) {
                    return $configuration;
                }

                if (($operation['operationType'] ?? null) === $operationType) {
                    return $operation;
                }
            }
        }

        return null;
    }

    public function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';
        $dialCode = (string) config('services.pawapay.dial_code', '242');

        // If the number already includes the dial code (full MSISDN), return it as-is.
        if (Str::startsWith($digits, $dialCode)) {
            return $digits;
        }

        // Bare national number without the country code, e.g. "064567890".
        // The leading zero after the dial code is part of the Congo MSISDN
        // (mobile range "06"/"05"), so it must be kept: "242064567890".
        return $dialCode.$digits;
    }

    public function amountAfterFee(int $amountInMinorUnits): int
    {
        $feePercent = (int) config('services.pawapay.fee_percent', 5);

        return intdiv($amountInMinorUnits * (100 - $feePercent), 100);
    }

    private function get(string $path, array $query = []): array
    {
        return $this->request(fn () => Http::withToken($this->token())
            ->acceptJson()
            ->timeout($this->timeout())
            ->get($this->apiUrl.$path, $query));
    }

    private function post(string $path, array $payload): array
    {
        return $this->request(fn () => Http::withToken($this->token())
            ->acceptJson()
            ->timeout($this->timeout())
            ->post($this->apiUrl.$path, $payload));
    }

    private function request(callable $request): array
    {
        $response = $request();

        if ($response->failed()) {
            throw new PawaPayException(
                'La communication avec PawaPay a échoué.',
                $response->status(),
                $response->body(),
            );
        }

        return $response->json() ?? [];
    }

    private function token(): string
    {
        return (string) config('services.pawapay.api_key');
    }

    private function timeout(): int
    {
        return (int) config('services.pawapay.timeout', 30);
    }
}
