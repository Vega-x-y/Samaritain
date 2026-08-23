<?php

namespace App\Http\Controllers\Owner;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    public function __construct(protected PawapayService $pawapay) {}

    /**
     * List all payouts initiated by this owner.
     */
    public function index()
    {
        $propertyIds = Property::where('created_by', auth()->id())->pluck('id');

        $payouts = Transaction::where('user_id', auth()->id())
            ->where('type', TransactionType::PAYOUT)
            ->latest()
            ->paginate(20);

        return view('pages.owner.payouts.index', compact('payouts'));
    }

    /**
     * Show the payout initiation form.
     */
    public function create()
    {
        return view('pages.owner.payouts.create');
    }

    /**
     * Initiate a pawaPay payout (send money to a Mobile Money number).
     *
     * Flow:
     *  1. Validate inputs.
     *  2. Predict provider from phone number via pawaPay API.
     *  3. Generate UUIDv4 (payoutId) and persist the Transaction as PENDING.
     *  4. Call pawaPay /v2/payouts.
     *  5. Redirect with status feedback.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'integer', 'min:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        // 1. Normalise the phone number and detect the provider.
        try {
            $prediction = $this->pawapay->predictProvider($validated['phone_number']);
        } catch (PawaPayException $e) {
            return back()
                ->withInput()
                ->withErrors(['phone_number' => 'Impossible de valider ce numéro de téléphone. Vérifiez le numéro et réessayez.']);
        }

        $provider = $prediction['provider'] ?? null;
        $msisdn = $prediction['phoneNumber'] ?? $validated['phone_number'];

        if (! $provider) {
            return back()
                ->withInput()
                ->withErrors(['phone_number' => 'Aucun opérateur Mobile Money détecté pour ce numéro.']);
        }

        // 2. Generate the UUID BEFORE any API call — idempotency key.
        $payoutId = (string) Str::uuid();

        // 3. Persist the transaction as PENDING — reconciliation anchor.
        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'type' => TransactionType::PAYOUT,
            'status' => TransactionStatus::PENDING,
            'amount' => $validated['amount'],
            'payout_id' => $payoutId,
            'provider' => $provider,
            'currency' => 'XAF',
        ]);

        // 4. Call pawaPay.
        try {
            $result = $this->pawapay->createPayout($payoutId, [
                'amount' => (string) $validated['amount'],
                'currency' => 'XAF',
                'country' => 'COG',
                'recipient' => [
                    'type' => 'MMO',
                    'accountDetails' => [
                        'phoneNumber' => $msisdn,
                        'provider' => $provider,
                    ],
                ],
                'customerTimestamp' => now()->toIso8601String(),
                'statementDescription' => $validated['description'] ?? 'Virement propriétaire Samaritain',
                'metadata' => [
                    ['transactionId' => $transaction->transaction_id],
                    ['userId' => (string) auth()->id()],
                ],
            ]);

            // Map PawaPay status to our enum (uppercase)
            $status = strtoupper($result['status'] ?? 'PENDING');
            $transaction->update([
                'status' => TransactionStatus::tryFrom($status) ?? TransactionStatus::PENDING,
                'raw_response' => $result,
            ]);
        } catch (PawaPayException $e) {
            // Do NOT mark as failed — leave PENDING for reconciliation.
            $transaction->update([
                'raw_response' => ['error' => $e->getMessage()],
            ]);

            return redirect()
                ->route('owner.payouts.index')
                ->with('warning', 'Le virement a été créé mais la confirmation pawaPay est en attente. Vérifiez l\'état dans quelques minutes.');
        }

        $status = strtoupper($result['status'] ?? 'UNKNOWN');

        if ($status === 'REJECTED') {
            return redirect()
                ->route('owner.payouts.index')
                ->with('error', 'Le virement a été refusé par pawaPay. Vérifiez le numéro et réessayez.');
        }

        return redirect()
            ->route('owner.payouts.index')
            ->with('success', 'Virement de '.number_format($validated['amount'], 0, ',', ' ').' FCFA initié avec succès vers '.$msisdn.'. Statut : '.$status.'.');
    }
}
