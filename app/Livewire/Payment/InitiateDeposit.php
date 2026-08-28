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

class InitiateDeposit extends Component
{
    public int $amount;

    public ?string $purpose = null;

    public ?string $referenceId = null;

    public string $phoneNumber = '';

    public string $provider = '';

    public ?Transaction $transaction = null;

    public bool $processing = false;

    public ?string $errorMessage = null;

    public function mount(int $amount, ?string $purpose = null, ?string $referenceId = null): void
    {
        $this->amount = $amount;
        $this->purpose = $purpose;
        $this->referenceId = $referenceId;
    }

    /** @return array<string, string> */
    public function getProvidersProperty(): array
    {
        try {
            return app(PawapayService::class)->activeProviders();
        } catch (PawaPayException) {
            return [];
        }
    }

    public function initiateDeposit(PawapayService $pawapay): void
    {
        $this->validate([
            'phoneNumber' => ['required', 'string', 'min:9', 'max:15'],
            'provider' => ['required', 'string', 'in:'.implode(',', array_keys($this->providers))],
        ]);

        $this->processing = true;
        $depositId = (string) Str::uuid();
        $currency = (string) config('services.pawapay.currency', 'XAF');
        $amount = $pawapay->amountAfterFee($this->amount);

        $this->transaction = Transaction::create([
            'transaction_id' => $depositId,
            'user_id' => auth()->id(),
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::PENDING,
            'amount' => $amount,
            'deposit_id' => $depositId,
            'provider' => $this->provider,
            'currency' => $currency,
            'raw_response' => [],
        ]);

        try {
            $response = $pawapay->initiateDeposit(new DepositRequest(
                depositId: $depositId,
                phoneNumber: $pawapay->normalizePhoneNumber($this->phoneNumber),
                provider: $this->provider,
                amount: number_format($amount / 100, 2, '.', ''),
                currency: $currency,
                clientReferenceId: $this->referenceId,
            ));

            $this->transaction->update([
                'status' => TransactionStatus::tryFrom(strtoupper((string) ($response['status'] ?? 'PENDING')))
                    ?? TransactionStatus::PENDING,
                'raw_response' => $response,
            ]);
        } catch (PawaPayException $exception) {
            $this->errorMessage = $exception->getMessage();
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.payment.initiate-deposit');
    }
}
