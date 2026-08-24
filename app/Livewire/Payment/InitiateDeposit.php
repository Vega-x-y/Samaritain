<?php

namespace App\Livewire\Payment;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Component for initiating a PawaPay deposit (collect payment from customer).
 *
 * Usage:
 * <livewire:payment.initiate-deposit :amount="5000" :purpose="'visit_pass'" :reference-id="$visitPass->id" />
 */
class InitiateDeposit extends Component
{
    // Props
    public int $amount;

    public ?string $purpose = null;

    public ?string $referenceId = null;

    public ?string $returnUrl = null;

    // Form fields
    public string $phoneNumber = '';

    public string $provider = '';

    public ?string $customerMessage = null;

    // State
    public ?Transaction $transaction = null;

    public bool $processing = false;

    public ?string $errorMessage = null;

    public ?string $redirectUrl = null;

    /**
     * Mount the component with props.
     */
    public function mount(
        int $amount,
        ?string $purpose = null,
        ?string $referenceId = null,
        ?string $returnUrl = null
    ): void {
        $this->amount = $amount;
        $this->purpose = $purpose;
        $this->referenceId = $referenceId;
        $this->returnUrl = $returnUrl ?? route('tenant.payments');

        // Pre-fill provider with default if available
        $providers = config('pawapay.providers', []);
        if (count($providers) > 0) {
            $this->provider = array_key_first($providers);
        }
    }

    /**
     * Get available providers from configuration.
     */
    public function getProvidersProperty(): array
    {
        return config('pawapay.providers', []);
    }

    /**
     * Initiate a direct deposit (form submission).
     */
    public function initiateDeposit(PawapayService $pawapay): void
    {
        $this->validate([
            'phoneNumber' => ['required', 'string', 'min:9', 'max:15'],
            'provider' => ['required', 'string', 'in:'.implode(',', array_keys($this->providers))],
            'customerMessage' => ['nullable', 'string', 'max:22'],
        ], [
            'phoneNumber.required' => 'Le numéro de téléphone est requis.',
            'phoneNumber.min' => 'Le numéro de téléphone doit contenir au moins 9 chiffres.',
            'phoneNumber.max' => 'Le numéro de téléphone est trop long.',
            'provider.required' => 'Veuillez sélectionner un opérateur.',
            'provider.in' => 'Opérateur invalide.',
            'customerMessage.max' => 'Le message ne peut pas dépasser 22 caractères.',
        ]);

        $this->processing = true;
        $this->errorMessage = null;
        $this->redirectUrl = null;

        try {
            // Normalize phone number
            $normalizedPhone = $pawapay->normalizePhoneNumber($this->phoneNumber);

            // Generate UUIDv4 for deposit
            $depositId = Str::uuid()->toString();

            // Create transaction record BEFORE calling API (idempotency)
            $this->transaction = Transaction::create([
                'transaction_id' => $depositId,
                'user_id' => auth()->id(),
                'type' => TransactionType::DEPOSIT,
                'status' => TransactionStatus::PENDING,
                'amount' => $this->amount,
                'deposit_id' => $depositId,
                'provider' => $this->provider,
                'currency' => config('pawapay.default_currency', 'XAF'),
                'raw_response' => [],
            ]);

            // Prepare deposit request
            $request = new DepositRequest(
                depositId: $depositId,
                phoneNumber: $normalizedPhone,
                provider: $this->provider,
                amount: (string) ($this->amount / 100), // Convert cents to currency units
                currency: config('pawapay.default_currency', 'XAF'),
                clientReferenceId: $this->referenceId,
                customerMessage: $this->customerMessage,
                metadata: $this->purpose ? [$this->purpose => $this->referenceId] : null,
            );

            // Call PawaPay API
            $response = $pawapay->initiateDeposit($request);

            // Update transaction with API response
            $status = TransactionStatus::tryFrom($response['status'] ?? '') ?? TransactionStatus::PENDING;

            $this->transaction->update([
                'status' => $status,
                'raw_response' => $response,
            ]);

            // Check if rejected
            if ($status === TransactionStatus::REJECTED) {
                $failureReason = $response['failureReason'] ?? [];
                $this->errorMessage = $failureReason['failureMessage']
                    ?? 'Le paiement a été rejeté. Veuillez réessayer.';
                $this->processing = false;

                return;
            }

            // Success - redirect to status page or show success message
            $this->dispatch('deposit-initiated', transactionId: $this->transaction->transaction_id);

            // Optionally redirect
            if ($this->returnUrl) {
                $this->redirect($this->returnUrl, navigate: true);
            }
        } catch (PawaPayException $e) {
            $this->errorMessage = $e->getMessage();
            $this->processing = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Une erreur inattendue est survenue. Veuillez réessayer.';
            $this->processing = false;
            logger()->error('Deposit initiation error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Initiate a payment page (hosted by PawaPay).
     */
    public function initiatePaymentPage(PawapayService $pawapay): void
    {
        $this->processing = true;
        $this->errorMessage = null;
        $this->redirectUrl = null;

        try {
            // Generate UUIDv4 for deposit
            $depositId = Str::uuid()->toString();

            // Create transaction record BEFORE calling API
            $this->transaction = Transaction::create([
                'transaction_id' => $depositId,
                'user_id' => auth()->id(),
                'type' => TransactionType::DEPOSIT,
                'status' => TransactionStatus::PENDING,
                'amount' => $this->amount,
                'deposit_id' => $depositId,
                'currency' => config('pawapay.default_currency', 'XAF'),
                'raw_response' => [],
            ]);

            // Create payment page
            $response = $pawapay->createPaymentPage(
                depositId: $depositId,
                returnUrl: $this->returnUrl,
                amount: (string) ($this->amount / 100),
                currency: config('pawapay.default_currency', 'XAF'),
                clientReferenceId: $this->referenceId
            );

            // Store redirect URL
            $this->redirectUrl = $response['redirectUrl'] ?? null;

            if (! $this->redirectUrl) {
                throw new \Exception('Aucune URL de redirection fournie par PawaPay.');
            }

            // Redirect to payment page
            $this->dispatch('redirecting-to-payment-page');
        } catch (PawaPayException $e) {
            $this->errorMessage = $e->getMessage();
            $this->processing = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Une erreur inattendue est survenue. Veuillez réessayer.';
            $this->processing = false;
            logger()->error('Payment page creation error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.payment.initiate-deposit');
    }
}
