<?php

namespace App\Http\Controllers;

use App\DataTransferObjects\Pawapay\DepositRequest;
use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Models\RentPayment;
use App\Models\Transaction;
use App\Models\VisitPass;
use App\Services\OwnerWalletService;
use App\Services\PawapayService;
use App\Services\RentPaymentService;
use App\Services\VisitPassService;
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
        protected OwnerWalletService $wallet,
        protected VisitPassService $visitPassService,
    ) {}

    public function getDepositForm(Request $request): View|RedirectResponse
    {
        $visitPass = $this->resolveVisitPass($request->query('visit_pass'));

        if ($visitPass instanceof RedirectResponse) {
            return $visitPass;
        }

        $rentPayment = $this->resolveRentPayment($request->query('rent_payment'));

        if ($rentPayment instanceof RedirectResponse) {
            return $rentPayment;
        }

        $providers_data = $this->getProvidersAvailable();

        if (empty($providers_data) || empty($providers_data['countries'][0]['providers'])) {
            return redirect()->back()->with('error', 'Aucun fournisseur de paiement disponible pour votre pays.');
        }

        return view('transactions.deposit-form', [
            'payment_config' => $providers_data['countries'][0],
            'branding' => $this->buildBranding($providers_data, 'DEPOSIT'),
            'visitPass' => $visitPass,
            'rentPayment' => $rentPayment,
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
            'branding' => $this->buildBranding($providers_data, 'PAYOUT'),
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

        $visitPass = $this->resolveVisitPass($request->input('visit_pass'));

        if ($visitPass instanceof RedirectResponse) {
            return $visitPass;
        }

        $rentPayment = $this->resolveRentPayment($request->input('rent_payment'));

        if ($rentPayment instanceof RedirectResponse) {
            return $rentPayment;
        }

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'visit_pass_id' => $visitPass?->id,
            'rent_payment_id' => $rentPayment?->id,
            'type' => TransactionType::DEPOSIT,
            'amount' => $rentPayment
                ? (int) $rentPayment->amount_due
                : ($visitPass ? (int) $visitPass->amount : (int) $request->amount),
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
            'amount' => (int) $request->amount,
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

            if ($transaction->type === TransactionType::PAYOUT) {
                // Handled by settle() above (reservation + debit/release).
            } elseif ($transaction->visit_pass_id && $transaction->visitPass) {
                // A visit pass purchase: mark the pass as paid (QR + PDF),
                // the wallet is NOT involved — the pass is not a wallet deposit.
                $this->visitPassService->handleSuccessfulPayment($transaction->visitPass);
            } elseif ($transaction->rent_payment_id && $transaction->rentPayment) {
                // A rent payment: settle() already credited the OWNER wallet
                // (via creditRentOwner). Mark the rent as paid + generate the receipt.
                // The tenant wallet is NOT credited.
                app(RentPaymentService::class)->handleSuccessfulPayment($transaction->rentPayment);
            } elseif ($transaction->type === TransactionType::DEPOSIT) {
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

    /**
     * Build the branding payload (company name, country flag, provider logos)
     * for the payment forms from the PawaPay active configuration.
     *
     * @param  array<string, mixed>  $providersData
     * @return array{companyName: ?string, countryName: ?string, flag: ?string, prefix: string, providers: array<int, array{provider: string, displayName: string, logo: ?string}>}
     */
    protected function buildBranding(array $providersData, string $type): array
    {
        $countryConfig = $providersData['countries'][0] ?? [];
        $displayName = $countryConfig['displayName'] ?? [];

        return [
            'companyName' => $providersData['companyName'] ?? null,
            'countryName' => $displayName['fr'] ?? $displayName['en'] ?? null,
            'flag' => isset($countryConfig['flag']) && is_string($countryConfig['flag']) ? $countryConfig['flag'] : null,
            'prefix' => (string) ($countryConfig['prefix'] ?? config('services.pawapay.dial_code', '242')),
            'providers' => $this->pawapay->providerBranding($countryConfig, $type),
        ];
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

    /**
     * Resolve an optional visit pass from the deposit context.
     *
     * Returns null when no pass is provided. Returns a RedirectResponse when the
     * pass is invalid, does not belong to the current user, or is already paid.
     */
    protected function resolveVisitPass(mixed $visitPassKey): VisitPass|null|RedirectResponse
    {
        if (! $visitPassKey) {
            return null;
        }

        $visitPass = VisitPass::where('uuid', $visitPassKey)->first();

        if (! $visitPass || $visitPass->user_id !== Auth::id()) {
            return redirect()->route('my-visit-passes.index')
                ->with('error', 'Ce pass visite est introuvable.');
        }

        if ($visitPass->isPaid()) {
            return redirect()->route('my-visit-passes.show', $visitPass)
                ->with('info', 'Ce pass visite est déjà payé.');
        }

        return $visitPass;
    }

    /**
     * Resolve an optional rent payment from the deposit context.
     *
     * Returns null when no rent payment is provided. Returns a RedirectResponse
     * when the rent payment is invalid, does not belong to the authenticated
     * user, its contract is not active, or it is already paid.
     */
    protected function resolveRentPayment(mixed $rentPaymentKey): RentPayment|null|RedirectResponse
    {
        if (! $rentPaymentKey) {
            return null;
        }

        $rentPayment = RentPayment::with('contract')->find($rentPaymentKey);

        if (! $rentPayment
            || $rentPayment->contract?->tenant_email !== Auth::user()?->email
            || $rentPayment->contract?->status !== 'active'
            || $rentPayment->isPaid()) {
            return redirect()->route('tenant.payments')
                ->with('error', 'Ce loyer est introuvable ou déjà payé.');
        }

        return $rentPayment;
    }
}
