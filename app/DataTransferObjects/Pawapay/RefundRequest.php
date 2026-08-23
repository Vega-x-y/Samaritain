<?php

namespace App\DataTransferObjects\Pawapay;

/**
 * Data Transfer Object for PawaPay refund requests.
 *
 * Represents the payload for POST /v2/refunds.
 */
readonly class RefundRequest
{
    public function __construct(
        public string $refundId,
        public string $depositId,
        public ?string $amount = null,
    ) {}

    /**
     * Convert to array for API request.
     */
    public function toArray(): array
    {
        $payload = [
            'refundId' => $this->refundId,
            'depositId' => $this->depositId,
        ];

        if ($this->amount !== null) {
            $payload['amount'] = $this->amount;
        }

        return $payload;
    }
}
