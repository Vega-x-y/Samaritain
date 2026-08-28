<?php

namespace App\Http\Controllers\Owner;

use App\DataTransferObjects\Pawapay\PayoutRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\OwnerWalletService;
use App\Services\PawapayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function __construct(
        private PawapayService $pawapay,
        private OwnerWalletService $wallets,
    ) {}

    public function index(Request $request): View
    {
        $payouts = Transaction::query()
            ->where('user_id', $request->user()->id)
            ->where('type', TransactionType::PAYOUT)
            ->latest()
            ->paginate(20);

        return view('pages.owner.payouts.index', compact('payouts'));
    }

    public function create(): View
    {
        $providers = $this->pawapay->activeProviders();
        $currency = config('services.pawapay.currency', 'XAF');
        $wallet = $this->wallets->balanceForOwner(auth()->id());

        return view('pages.owner.payouts.create', compact('providers', 'currency', 'wallet'));
    }

    public function store(Request $request): RedirectResponse
    {
        $providers = $this->pawapay->activeProviders();
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'min:9', 'max:15'],
            'provider' => ['required', 'string', 'in:'.implode(',', array_keys($providers))],
            'amount' => ['required', 'integer', 'min:100'],
            'description' => ['nullable', 'string', 'max:22'],
        ]);

        $payoutId = (string) Str::uuid();
        $amount = $this->pawapay->amountAfterFee((int) $validated['amount']);
        $currency = config('services.pawapay.currency', 'XAF');
        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'type' => TransactionType::PAYOUT,
            'status' => TransactionStatus::PENDING,
            'amount' => $amount,
            'payout_id' => $payoutId,
            'provider' => $validated['provider'],
            'currency' => $currency,
        ]);

        try {
            $this->wallets->reservePayout($transaction);
        } catch (\RuntimeException $exception) {
            $transaction->delete();

            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        }

        try {
            $response = $this->pawapay->initiatePayout(new PayoutRequest(
                payoutId: $payoutId,
                phoneNumber: $this->pawapay->normalizePhoneNumber($validated['phone_number']),
                provider: $validated['provider'],
                amount: number_format($amount / 100, 2, '.', ''),
                currency: $currency,
                clientReferenceId: (string) $transaction->transaction_id,
                customerMessage: $validated['description'] ?? 'Retrait Samaritain',
            ));

            $status = TransactionStatus::tryFrom(strtoupper((string) ($response['status'] ?? 'PENDING')))
                ?? TransactionStatus::PENDING;
            $transaction->update(['status' => $status, 'raw_response' => $response]);
            if ($status->isFinal()) {
                $this->wallets->settle($transaction, $status);
            }
        } catch (PawaPayException $exception) {
            $transaction->update(['raw_response' => ['error' => $exception->getMessage()]]);

            return to_route('owner.payouts.index')
                ->with('warning', 'Le retrait est enregistré et sera vérifié ultérieurement.');
        }

        return to_route('owner.payouts.index')
            ->with('success', 'Le retrait a été initié.');
    }
}
