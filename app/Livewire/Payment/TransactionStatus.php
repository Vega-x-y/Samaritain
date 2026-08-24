<?php

namespace App\Livewire\Payment;

use App\Enums\TransactionStatus as TransactionStatusEnum;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Component for displaying transaction status details and refreshing status from PawaPay.
 *
 * Usage:
 * <livewire:payment.transaction-status :transaction-id="$transactionId" />
 */
class TransactionStatus extends Component
{
    public string $transactionId;

    public ?Transaction $transaction = null;

    public bool $processing = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public int $pollingInterval = 5000; // 5 seconds in milliseconds

    /**
     * Mount the component.
     */
    public function mount(string $transactionId): void
    {
        $this->transactionId = $transactionId;
        $this->loadTransaction();
    }

    /**
     * Load the transaction from the database.
     */
    public function loadTransaction(): void
    {
        $this->transaction = Transaction::with('user')
            ->where('transaction_id', $this->transactionId)
            ->firstOrFail();

        // Ensure user can view this transaction
        if ($this->transaction->user_id !== auth()->id()) {
            abort(403, 'Vous n\'êtes pas autorisé à voir cette transaction.');
        }
    }

    /**
     * Check if transaction status should be auto-refreshed.
     */
    public function getShouldPollProperty(): bool
    {
        return $this->transaction && $this->transaction->status->isPending();
    }

    /**
     * Refresh the transaction status from PawaPay API.
     */
    public function refreshStatus(PawapayService $pawapay): void
    {
        $this->processing = true;
        $this->errorMessage = null;
        $this->successMessage = null;

        try {
            $response = null;

            // Call appropriate API endpoint based on transaction type
            match ($this->transaction->type) {
                TransactionType::DEPOSIT => $response = $pawapay->getDepositStatus($this->transaction->deposit_id),
                TransactionType::PAYOUT => $response = $pawapay->getPayoutStatus($this->transaction->payout_id),
                TransactionType::REFUND => $response = $pawapay->getRefundStatus($this->transaction->refund_id),
            };

            if ($response && isset($response['status'])) {
                // Check if data is found
                if ($response['status'] === 'NOT_FOUND') {
                    $this->errorMessage = 'Transaction non trouvée chez PawaPay.';
                    $this->processing = false;

                    return;
                }

                // Update transaction with new status
                $transactionData = $response['data'] ?? [];
                $newStatus = TransactionStatusEnum::tryFrom($transactionData['status'] ?? '')
                    ?? $this->transaction->status;

                $this->transaction->update([
                    'status' => $newStatus,
                    'raw_response' => array_merge($this->transaction->raw_response ?? [], $transactionData),
                ]);

                $this->successMessage = 'Statut mis à jour avec succès.';
                $this->loadTransaction(); // Refresh the model
            }

            $this->processing = false;
        } catch (PawaPayException $e) {
            $this->errorMessage = $e->getMessage();
            $this->processing = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors de la vérification du statut.';
            $this->processing = false;
            logger()->error('Status refresh error', [
                'transaction_id' => $this->transactionId,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retry the failed transaction by creating a new one.
     */
    public function retry(PawapayService $pawapay): void
    {
        $this->processing = true;
        $this->errorMessage = null;

        try {
            // Only allow retry for failed transactions
            if (! $this->transaction->is_failed) {
                $this->errorMessage = 'Seules les transactions échouées peuvent être réessayées.';
                $this->processing = false;

                return;
            }

            // Generate new UUID for retry
            $newTransactionId = Str::uuid()->toString();

            // Create new transaction with same details
            $newTransaction = Transaction::create([
                'transaction_id' => $newTransactionId,
                'user_id' => $this->transaction->user_id,
                'visit_pass_id' => $this->transaction->visit_pass_id,
                'rent_payment_id' => $this->transaction->rent_payment_id,
                'type' => $this->transaction->type,
                'status' => TransactionStatusEnum::PENDING,
                'amount' => $this->transaction->amount,
                'currency' => $this->transaction->currency,
                'provider' => $this->transaction->provider,
                'deposit_id' => $this->transaction->type === TransactionType::DEPOSIT ? $newTransactionId : null,
                'payout_id' => $this->transaction->type === TransactionType::PAYOUT ? $newTransactionId : null,
                'refund_id' => $this->transaction->type === TransactionType::REFUND ? $newTransactionId : null,
                'raw_response' => [],
            ]);

            // Redirect to the new transaction
            $this->redirect(route('tenant.transactions.show', $newTransaction), navigate: true);
        } catch (\Exception $e) {
            $this->errorMessage = 'Erreur lors de la création de la nouvelle transaction.';
            $this->processing = false;
            logger()->error('Transaction retry error', [
                'original_transaction_id' => $this->transactionId,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format raw response for display.
     */
    public function getFormattedRawResponseProperty(): string
    {
        if (! $this->transaction->raw_response) {
            return 'Aucune donnée disponible';
        }

        return json_encode($this->transaction->raw_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function render()
    {
        return view('livewire.payment.transaction-status');
    }
}
