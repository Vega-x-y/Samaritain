<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Transaction model for PawaPay payments.
 *
 * Stores deposits (customer pays you), payouts (you pay customer),
 * and refunds (refund a completed deposit).
 *
 * @property string $transaction_id Primary key (UUIDv4)
 * @property int|null $user_id
 * @property int|null $visit_pass_id
 * @property int|null $rent_payment_id
 * @property TransactionType $type
 * @property TransactionStatus $status
 * @property int $amount Amount in cents/minor currency units
 * @property string|null $deposit_id PawaPay deposit ID (UUIDv4)
 * @property string|null $payout_id PawaPay payout ID (UUIDv4)
 * @property string|null $refund_id PawaPay refund ID (UUIDv4)
 * @property string|null $provider Mobile money provider code (e.g. MTN_MOMO_COG)
 * @property string|null $currency ISO 4217 currency code
 * @property array|null $raw_response Full PawaPay response/callback data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read VisitPass|null $visitPass
 * @property-read RentPayment|null $rentPayment
 */
class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'transaction_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'visit_pass_id',
        'rent_payment_id',
        'artisan_request_id',
        'type',
        'status',
        'amount',
        'deposit_id',
        'payout_id',
        'refund_id',
        'provider',
        'currency',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'integer',
        'raw_response' => 'array',
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
    ];

    /**
     * The attributes that should be appended to the model's array form.
     */
    protected $appends = [
        'is_completed',
        'is_pending',
        'is_failed',
    ];

    /**
     * Specify which attributes should use UUIDs.
     */
    public function uniqueIds(): array
    {
        return ['transaction_id'];
    }

    /**
     * Get the user who owns this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the visit pass associated with this transaction.
     */
    public function visitPass(): BelongsTo
    {
        return $this->belongsTo(VisitPass::class);
    }

    /**
     * Get the rent payment associated with this transaction.
     */
    public function rentPayment(): BelongsTo
    {
        return $this->belongsTo(RentPayment::class);
    }

    /**
     * Get the artisan request associated with this transaction.
     */
    public function artisanRequest(): BelongsTo
    {
        return $this->belongsTo(ArtisanRequest::class);
    }

    /**
     * Check if this transaction is completed (successful).
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status->isSuccessful();
    }

    /**
     * Check if this transaction is still pending.
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Check if this transaction has failed.
     */
    public function getIsFailedAttribute(): bool
    {
        return $this->status === TransactionStatus::FAILED
            || $this->status === TransactionStatus::REJECTED;
    }

    /**
     * Get the PawaPay transaction ID (deposit_id, payout_id, refund_id, or transaction_id).
     */
    public function getPawapayIdAttribute(): ?string
    {
        return $this->deposit_id ?? $this->payout_id ?? $this->refund_id ?? $this->transaction_id;
    }

    /**
     * Get the failure reason from raw_response if available.
     */
    public function getFailureReasonAttribute(): ?string
    {
        if (! $this->raw_response) {
            return null;
        }

        $failureReason = $this->raw_response['failureReason'] ?? null;

        if (! $failureReason) {
            return null;
        }

        // Extract failureCode if available
        return $failureReason['failureCode'] ?? $failureReason['failureMessage'] ?? 'Unknown error';
    }

    /**
     * Scope to only deposits.
     */
    public function scopeDeposits($query)
    {
        return $query->where('type', TransactionType::DEPOSIT);
    }

    /**
     * Scope to only payouts.
     */
    public function scopePayouts($query)
    {
        return $query->where('type', TransactionType::PAYOUT);
    }

    /**
     * Scope to only refunds.
     */
    public function scopeRefunds($query)
    {
        return $query->where('type', TransactionType::REFUND);
    }

    /**
     * Scope to only completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatus::COMPLETED);
    }

    /**
     * Scope to only pending transactions.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            TransactionStatus::PENDING,
            TransactionStatus::SUBMITTED,
            TransactionStatus::ACCEPTED,
            TransactionStatus::ENQUEUED,
        ]);
    }

    /**
     * Scope to only failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [
            TransactionStatus::FAILED,
            TransactionStatus::REJECTED,
        ]);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return TransactionFactory::new();
    }
}
