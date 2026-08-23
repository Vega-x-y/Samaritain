<?php

namespace App\DataTransferObjects\Pawapay;

/**
 * Data Transfer Object for PawaPay payout requests.
 *
 * Represents the payload for POST /v2/payouts.
 */
readonly class PayoutRequest
{
    public function __construct(
        public string $payoutId,
        public string $phoneNumber,
        public string $provider,
        public string $amount,
        public string $currency,
        public ?string $clientReferenceId = null,
        public ?string $customerMessage = null,
        public ?array $metadata = null,
    ) {}

    /**
     * Convert to array for API request.
     */
    public function toArray(): array
    {
        $payload = [
            'payoutId' => $this->payoutId,
            'recipient' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $this->phoneNumber,
                    'provider' => $this->provider,
                ],
            ],
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];

        if ($this->clientReferenceId !== null) {
            $payload['clientReferenceId'] = $this->clientReferenceId;
        }

        if ($this->customerMessage !== null) {
            $payload['customerMessage'] = $this->customerMessage;
        }

        if ($this->metadata !== null && count($this->metadata) > 0) {
            $payload['metadata'] = $this->metadata;
        }

        return $payload;
    }
}
