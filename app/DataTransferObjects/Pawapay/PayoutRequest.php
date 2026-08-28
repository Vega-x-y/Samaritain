<?php

namespace App\DataTransferObjects\Pawapay;

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

    public function toArray(): array
    {
        return array_filter([
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
            'clientReferenceId' => $this->clientReferenceId,
            'customerMessage' => $this->customerMessage,
            'metadata' => $this->metadata,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
