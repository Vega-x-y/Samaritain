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

    public function getActiveConfiguration(): array
    {
        return $this->get('/active-conf');
    }

    /** @return array<string, string> */
    public function activeProviders(): array
    {
        $configuration = $this->getActiveConfiguration();
        $country = (string) config('services.pawapay.country', 'COG');
        $entries = $configuration[$country] ?? $configuration['providers'] ?? $configuration;
        $providers = [];

        foreach ((array) $entries as $key => $entry) {
            if (is_string($entry)) {
                $providers[$entry] = $entry;

                continue;
            }

            if (! is_array($entry)) {
                continue;
            }

            $code = $entry['provider'] ?? $entry['providerName'] ?? $entry['name'] ?? (is_string($key) ? $key : null);
            $label = $entry['displayName'] ?? $entry['providerName'] ?? $entry['name'] ?? $code;

            if (is_string($code) && $code !== '') {
                $providers[$code] = is_string($label) && $label !== '' ? $label : $code;
            }
        }

        return $providers;
    }

    public function normalizePhoneNumber(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?? '';
        $dialCode = (string) config('services.pawapay.dial_code', '242');

        if (Str::startsWith($digits, '0')) {
            $digits = $dialCode.substr($digits, 1);
        }

        return Str::startsWith($digits, $dialCode) ? $digits : $dialCode.$digits;
    }

    public function amountAfterFee(int $amountInMinorUnits): int
    {
        $feePercent = (int) config('services.pawapay.fee_percent', 5);

        return intdiv($amountInMinorUnits * (100 - $feePercent), 100);
    }

    private function get(string $path): array
    {
        return $this->request(fn () => Http::withToken($this->token())
            ->acceptJson()
            ->timeout($this->timeout())
            ->get($this->apiUrl.$path));
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
