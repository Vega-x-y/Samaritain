<?php

namespace App\Enums;

/**
 * PawaPay transaction types.
 *
 * - DEPOSIT: Collect payment from a customer (customer pays you)
 * - PAYOUT: Send money to a customer (you pay customer)
 * - REFUND: Refund a completed deposit
 */
enum TransactionType: string
{
    case DEPOSIT = 'DEPOSIT';
    case PAYOUT = 'PAYOUT';
    case REFUND = 'REFUND';

    /**
     * Get a human-readable label for this transaction type.
     */
    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Dépôt',
            self::PAYOUT => 'Retrait',
            self::REFUND => 'Remboursement',
        };
    }

    /**
     * Check if this transaction type adds money to your account.
     */
    public function isIncoming(): bool
    {
        return $this === self::DEPOSIT;
    }

    /**
     * Check if this transaction type removes money from your account.
     */
    public function isOutgoing(): bool
    {
        return in_array($this, [self::PAYOUT, self::REFUND], true);
    }
}
