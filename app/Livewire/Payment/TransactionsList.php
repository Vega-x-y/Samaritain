<?php

namespace App\Livewire\Payment;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Component for listing user transactions with filtering and polling.
 *
 * Usage:
 * <livewire:payment.transactions-list />
 */
class TransactionsList extends Component
{
    use WithPagination;

    public string $filterType = 'all';

    public int $pollingInterval = 10000; // 10 seconds in milliseconds

    /**
     * Update the filter type.
     */
    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    /**
     * Get filtered transactions query.
     */
    public function getTransactionsProperty()
    {
        $query = Transaction::query()
            ->where('user_id', auth()->id())
            ->latest();

        // Apply type filter
        if ($this->filterType !== 'all') {
            $type = match ($this->filterType) {
                'deposits' => TransactionType::DEPOSIT,
                'payouts' => TransactionType::PAYOUT,
                'refunds' => TransactionType::REFUND,
                default => null,
            };

            if ($type) {
                $query->where('type', $type);
            }
        }

        return $query->paginate(10);
    }

    /**
     * Check if there are any pending transactions.
     */
    public function getHasPendingTransactionsProperty(): bool
    {
        return Transaction::query()
            ->where('user_id', auth()->id())
            ->pending()
            ->exists();
    }

    public function render()
    {
        return view('livewire.payment.transactions-list');
    }
}
