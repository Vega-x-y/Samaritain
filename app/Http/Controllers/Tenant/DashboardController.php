<?php

namespace App\Http\Controllers\Tenant;

use App\Events\ContractFullySigned;
use App\Events\ContractSigned;
use App\Exceptions\PawaPayException;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Intervention;
use App\Models\OwnerDocument;
use App\Models\RentPayment;
use App\Models\Transaction;
use App\Notifications\ContractCompletedNotification;
use App\Notifications\ContractSignedNotification;
use App\Services\ContractSignatureService;
use App\Services\PawapayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $contracts = Contract::where('tenant_email', $user->email)
            ->with('property:id,title,address')
            ->get();
        $activeContract = $contracts->where('status', 'active')->first();

        $totalPaid = 0;
        $totalDue = 0;
        $nextPayment = null;
        $latePayments = collect();
        $recentPayments = collect();

        if ($activeContract) {
            $payments = RentPayment::where('contract_id', $activeContract->id)->get();
            $totalPaid = $payments->where('status', 'paid')->sum('amount_paid');
            $totalDue = $payments->sum('amount_due');
            $nextPayment = RentPayment::where('contract_id', $activeContract->id)
                ->where('status', 'unpaid')
                ->where('due_date', '>=', now())
                ->orderBy('due_date')
                ->first();
            $latePayments = RentPayment::where('contract_id', $activeContract->id)
                ->where('status', 'unpaid')
                ->where('due_date', '<', now())
                ->orderBy('due_date')
                ->get();
            $recentPayments = RentPayment::where('contract_id', $activeContract->id)
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->take(5)
                ->get();
        }

        $interventions = collect();
        if ($activeContract) {
            $interventions = Intervention::where('property_id', $activeContract->property_id)
                ->latest()
                ->take(5)
                ->get();
        }

        $propertyIds = $contracts->pluck('property_id');
        $documents = OwnerDocument::whereIn('property_id', $propertyIds)
            ->latest()
            ->take(5)
            ->get();

        return view('pages.tenant.dashboard', compact(
            'contracts', 'activeContract', 'totalPaid', 'totalDue', 'nextPayment', 'latePayments', 'interventions', 'documents', 'recentPayments'
        ));
    }

    public function contracts()
    {
        $user = auth()->user();
        $contracts = Contract::where('tenant_email', $user->email)
            ->with('property:id,title,address')
            ->latest()
            ->get();

        return view('pages.tenant.contracts', compact('contracts'));
    }

    public function show(Contract $contract)
    {
        $user = auth()->user();

        if ($contract->tenant_email !== $user->email) {
            abort(403);
        }

        $contract->load('property', 'signatures.user');

        return view('pages.tenant.contract-show', compact('contract'));
    }

    public function payments()
    {
        $user = auth()->user();
        $contract = Contract::where('tenant_email', $user->email)
            ->where('status', 'active')
            ->first();

        $payments = collect();
        if ($contract) {
            $payments = RentPayment::where('contract_id', $contract->id)
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->get();
        }

        return view('pages.tenant.payments', compact('contract', 'payments'));
    }

    /**
     * Show the payment summary before opening the hosted payment page.
     */
    public function payRentPayment(RentPayment $rentPayment, PawapayService $pawapay)
    {
        $user = auth()->user();
        $contract = $rentPayment->contract;

        // The tenant must own the contract and it must be active.
        if ($contract->tenant_email !== $user->email || $contract->status !== 'active') {
            abort(403, 'Vous ne pouvez pas payer ce loyer.');
        }

        if ($rentPayment->isPaid()) {
            return redirect()->route('tenant.payments')
                ->with('info', 'Ce loyer est déjà payé.');
        }

        $currency = config('services.pawapay.currency', 'XAF');

        return view('pages.tenant.rent-pay', compact('rentPayment', 'contract', 'currency'));
    }

    /**
     * Redirect the tenant to pawaPay's hosted payment page.
     *
     * Generate and persist the UUIDv4 depositId BEFORE calling pawaPay. On an
     * HTTP failure the transaction is kept as pending — never failed.
     */
    public function initiateRentPayment(Request $request, RentPayment $rentPayment, PawapayService $pawapay)
    {
        $user = auth()->user();
        $contract = $rentPayment->contract;

        if ($contract->tenant_email !== $user->email || $contract->status !== 'active') {
            abort(403, 'Vous ne pouvez pas payer ce loyer.');
        }

        if ($rentPayment->isPaid()) {
            return redirect()->route('tenant.payments')
                ->with('info', 'Ce loyer est déjà payé.');
        }

        // Generate and persist the UUIDv4 idempotency key before any API call.
        $depositId = (string) Str::uuid();
        $currency = config('services.pawapay.currency', 'XAF');

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'rent_payment_id' => $rentPayment->id,
            'status' => 'pending',
            'amount' => $rentPayment->amount_due,
            'deposit_id' => $depositId,
            'currency' => $currency,
        ]);

        $rentPayment->update(['transaction_id' => $transaction->transaction_id]);

        try {
            $result = $pawapay->createPaymentPage(
                depositId: $depositId,
                returnUrl: route('transactions.callback', $transaction),
                amount: $transaction->amount,
                currency: $transaction->currency,
                clientReferenceId: $rentPayment->contract_id.'-'.$rentPayment->id,
            );

            $transaction->update([
                'status' => 'pending',
                'raw_response' => $result,
            ]);

            return redirect()->away($result['redirectUrl']);
        } catch (PawaPayException $e) {
            // Do NOT mark as failed — leave as pending for reconciliation.
            $transaction->update([
                'raw_response' => ['error' => $e->getMessage(), 'status_code' => $e->getStatusCode()],
            ]);
        }

        return redirect()->route('transactions.pending', $transaction);
    }

    public function interventions()
    {
        $user = auth()->user();
        $contract = Contract::where('tenant_email', $user->email)
            ->where('status', 'active')
            ->first();

        $interventions = collect();
        if ($contract) {
            $interventions = Intervention::where('property_id', $contract->property_id)
                ->latest()
                ->paginate(15);
        }

        return view('pages.tenant.interventions', compact('contract', 'interventions'));
    }

    public function documents()
    {
        $user = auth()->user();
        $contracts = Contract::where('tenant_email', $user->email)->get();
        $propertyIds = $contracts->pluck('property_id');

        $documents = OwnerDocument::whereIn('property_id', $propertyIds)
            ->latest()
            ->paginate(15);

        return view('pages.tenant.documents', compact('documents'));
    }

    public function sign(Request $request, Contract $contract, ContractSignatureService $signatureService)
    {
        $user = auth()->user();

        if ($contract->tenant_email !== $user->email) {
            abort(403, 'Vous ne pouvez pas signer ce contrat.');
        }

        $request->validate([
            'signature' => ['required', 'string'],
        ]);

        $signature = $signatureService->createSignature(
            $contract,
            $user,
            'tenant',
            $request->input('signature')
        );

        // Check if contract is now fully signed
        $contract->refresh();
        if ($contract->isFullySigned()) {
            event(new ContractFullySigned($contract));
            $contract->creator->notify(new ContractSignedNotification($contract, 'tenant'));
            $user->notify(new ContractCompletedNotification($contract));

            // Generate final signed PDF via the signature service
            $signatureService->generateSignedPdfDocument($contract);
        } else {
            event(new ContractSigned($contract, $user, 'tenant'));
            $contract->creator->notify(new ContractSignedNotification($contract, 'tenant'));
        }

        return redirect()->route('tenant.contracts.show', $contract)
            ->with('success', 'Contrat signé avec succès.');
    }

    public function downloadPdf(Contract $contract)
    {
        $user = auth()->user();

        if ($contract->tenant_email !== $user->email) {
            abort(403, 'Vous ne pouvez pas télécharger ce contrat.');
        }

        $contract->load('property.city', 'signatures.user');
        $property = $contract->property;

        $pdf = Pdf::loadView('pages.owner.pdf.lease-contract', compact('contract', 'property'));
        $fileName = 'contrat_bail_'.$contract->id.'_'.$contract->tenant_name.'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
    }

    public function downloadDocument(OwnerDocument $document)
    {
        $user = auth()->user();

        $propertyIds = Contract::where('tenant_email', $user->email)->pluck('property_id');

        if (! $propertyIds->contains($document->property_id)) {
            abort(403, 'Vous ne pouvez pas télécharger ce document.');
        }

        if (! Storage::exists($document->file_path)) {
            abort(404, 'Fichier introuvable.');
        }

        $filename = str_replace(['/', '\\'], '-', $document->name);

        return Storage::download($document->file_path, $filename);
    }
}
