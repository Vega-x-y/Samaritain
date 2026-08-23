<?php

namespace App\DataTransferObjects\Pawapay;

/**
 * Data Transfer Object for PawaPay deposit requests.
 *
 * Represents the payload for POST /v2/deposits.
 */
readonly class DepositRequest
{
    public function __construct(
        public string $depositId,
        public string $phoneNumber,
        public string $provider,
        public string $amount,
        public string $currency,
        public ?string $clientReferenceId = null,
        public ?string $customerMessage = null,
        public ?array $metadata = null,
        public ?string $preAuthorisationCode = null,
    ) {}

    /**
     * Convert to array for API request.
     */
    public function toArray(): array
    {
        $payload = [
            'depositId' => $this->depositId,
            'payer' => [
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

        if ($this->preAuthorisationCode !== null) {
            $payload['preAuthorisationCode'] = $this->preAuthorisationCode;
        }

        return $payload;
    }
}
