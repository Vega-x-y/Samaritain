<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\Transaction;
use App\Services\OwnerWalletService;
use App\Services\PawapayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionsController extends Controller
{
    public function __construct(
        protected PawapayService $pawapay,
        protected OwnerWalletService $wallet
    ) {}

    public function getDepositForm(): View|RedirectResponse
    {
        $providers_data = $this->getProvidersAvailable();

        if (empty($providers_data) || empty($providers_data['countries'][0]['providers'])) {
            return redirect()->back()->with('error', 'Aucun fournisseur de paiement disponible pour votre pays.');
        }

        return view('transactions.deposit-form', [
            'payment_config' => $providers_data['countries'][0],
        ]);
    }

    public function getWithdrawForm(): View|RedirectResponse
    {
        $balance = $this->wallet->balanceForOwner((int) Auth::id())->available_balance;

        if ($balance < 1000) {
            return redirect()->back()->with('error', 'Votre solde est insuffisant pour effectuer un retrait. Le montant minimum de retrait est de 1000.');
        }

        $providers_data = $this->getProvidersAvailable('PAYOUT');

        if (empty($providers_data) || empty($providers_data['countries'][0]['providers'])) {
            return redirect()->back()->with('error', "Aucun fournisseur de transfert d'argent disponible pour votre pays.");
        }

        return view('transactions.withdraw-form', [
            'payment_config' => $providers_data['countries'][0],
            'balance' => $balance,
        ]);
    }

    public function initDeposit(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone' => 'required|min:9',
            'provider' => 'required',
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'type' => TransactionType::DEPOSIT,
            'amount' => (int) round((float) $request->amount * 100),
            'status' => TransactionStatus::PENDING,
            'provider' => $request->provider,
            'currency' => config('services.pawapay.currency'),
            'deposit_id' => Str::uuid()->toString(),
        ]);

        try {
            $phone = $this->pawapay->normalizePhoneNumber($request->phone);

            $response = $this->pawapay->initiateDeposit(new DepositRequest(
                depositId: $transaction->deposit_id,
                phoneNumber: $phone,
                provider: $transaction->provider,
                amount: (string) $transaction->amount,
                currency: $transaction->currency,
            ));

            if (($response['status'] ?? null) === 'ACCEPTED') {
                return redirect()->route('transactions.deposit.status', $transaction);
            }

            $transaction->update([
                'status' => TransactionStatus::REJECTED,
                'raw_response' => $response,
            ]);

            return redirect()->route('transactions.deposit.status', $transaction);
        } catch (PawaPayException|\Throwable $th) {
            Log::warning('PawaPay init deposit failed', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $th->getMessage(),
            ]);

            return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayer !");
        }
    }

    public function initWithdraw(Request $request): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:2',
            'phone' => 'required|min:9',
            'provider' => 'required',
        ]);

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'type' => TransactionType::PAYOUT,
            'amount' => (int) round((float) $request->amount * 100),
            'status' => TransactionStatus::PENDING,
            'provider' => $request->provider,
            'currency' => config('services.pawapay.currency'),
            'payout_id' => Str::uuid()->toString(),
        ]);

        try {
            $phone = $this->pawapay->normalizePhoneNumber($request->phone);

            $response = $this->pawapay->initiatePayout(new PayoutRequest(
                payoutId: $transaction->payout_id,
                phoneNumber: $phone,
                provider: $transaction->provider,
                amount: (string) $transaction->amount,
                currency: $transaction->currency,
            ));

            if (($response['status'] ?? null) === 'ACCEPTED') {
                $this->wallet->reservePayout($transaction);

                return redirect()->route('transactions.withdraw.status', $transaction);
            }

            $transaction->update([
                'status' => TransactionStatus::REJECTED,
                'raw_response' => $response,
            ]);

            return redirect()->route('transactions.withdraw.status', $transaction);
        } catch (PawaPayException|\Throwable $th) {
            Log::warning('PawaPay init withdraw failed', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $th->getMessage(),
            ]);

            return redirect()->back()->with('error', "une erreur s'est produite, veuillez ressayer !");
        }
    }

    public function getTransactionStatus(Transaction $transaction): View
    {
        abort_unless($transaction->user_id === Auth::id(), 403);

        $data = $this->verifyTransactionStatus($transaction);

        if (! $data) {
            return view('transactions.status', compact('transaction'));
        }

        $status = TransactionStatus::tryFrom(strtoupper((string) ($data['status'] ?? '')));

        if ($status === TransactionStatus::COMPLETED) {
            $this->wallet->settle($transaction, TransactionStatus::COMPLETED);

            if ($transaction->type === TransactionType::DEPOSIT) {
                $this->wallet->creditDeposit($transaction);
            }
        } elseif ($status === TransactionStatus::FAILED) {
            $transaction->update([
                'status' => TransactionStatus::FAILED,
                'raw_response' => $data,
            ]);
        }

        $transaction->refresh();

        return view('transactions.status', compact('transaction'));
    }

    protected function getProvidersAvailable(string $type = 'DEPOSIT'): array
    {
        try {
            return $this->pawapay->getActiveConfiguration(
                (string) config('services.pawapay.country'),
                $type
            );
        } catch (PawaPayException $th) {
            Log::warning('PawaPay active configuration failed', ['error' => $th->getMessage()]);

            return [];
        }
    }

    protected function verifyTransactionStatus(Transaction $transaction): ?array
    {
        try {
            $response = $transaction->type === TransactionType::PAYOUT
                ? $this->pawapay->getPayoutStatus((string) $transaction->payout_id)
                : $this->pawapay->getDepositStatus((string) $transaction->deposit_id);

            return ($response['status'] ?? null) === 'FOUND'
                ? ($response['data'] ?? null)
                : null;
        } catch (PawaPayException $th) {
            Log::warning('PawaPay status verification failed', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $th->getMessage(),
            ]);

            return null;
        }
    }
}
