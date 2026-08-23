<?php

namespace App\Enums;

/**
 * PawaPay transaction status values.
 *
 * These map to the statuses returned by PawaPay API and callbacks.
 * Status lifecycle:
 * - PENDING → deposit/payout initiated, awaiting customer action
 * - SUBMITTED → sent to provider, processing
 * - ACCEPTED → initiation accepted by PawaPay (not final!)
 * - COMPLETED → successful, money transferred
 * - FAILED → failed, no money moved
 * - ENQUEUED → payout queued due to limits
 * - CANCELLED → payout manually cancelled
 * - DUPLICATE_IGNORED → duplicate depositId/payoutId
 * - REJECTED → initiation rejected, see failureReason
 */
enum TransactionStatus: string
{
    case PENDING = 'PENDING';
    case SUBMITTED = 'SUBMITTED';
    case ACCEPTED = 'ACCEPTED';
    case COMPLETED = 'COMPLETED';
    case FAILED = 'FAILED';
    case ENQUEUED = 'ENQUEUED';
    case CANCELLED = 'CANCELLED';
    case DUPLICATE_IGNORED = 'DUPLICATE_IGNORED';
    case REJECTED = 'REJECTED';

    /**
     * Check if this status is final (no further updates expected).
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
            self::REJECTED,
        ], true);
    }

    /**
     * Check if this status means the transaction succeeded.
     */
    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if this status means the transaction is still in progress.
     */
    public function isPending(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::SUBMITTED,
            self::ACCEPTED,
            self::ENQUEUED,
        ], true);
    }

    /**
     * Get a human-readable label for this status.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::SUBMITTED => 'Soumis',
            self::ACCEPTED => 'Accepté',
            self::COMPLETED => 'Terminé',
            self::FAILED => 'Échoué',
            self::ENQUEUED => 'En file',
            self::CANCELLED => 'Annulé',
            self::DUPLICATE_IGNORED => 'Doublon ignoré',
            self::REJECTED => 'Rejeté',
        };
    }

    /**
     * Get a CSS/Flux variant for this status.
     */
    public function variant(): string
    {
        return match ($this) {
            self::COMPLETED => 'success',
            self::FAILED, self::REJECTED, self::CANCELLED => 'danger',
            self::PENDING, self::SUBMITTED, self::ACCEPTED, self::ENQUEUED => 'warning',
            self::DUPLICATE_IGNORED => 'info',
        };
    }
}
