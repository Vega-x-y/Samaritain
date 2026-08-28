<?php

namespace App\Http\Controllers\Tenant;

use App\Events\ContractFullySigned;
use App\Events\ContractSigned;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Intervention;
use App\Models\OwnerDocument;
use App\Models\RentPayment;
use App\Notifications\ContractCompletedNotification;
use App\Notifications\ContractSignedNotification;
use App\Services\ContractSignatureService;
use App\Support\BrandingHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

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

    public function payments(): View
    {
        $contract = Contract::where('tenant_email', auth()->user()->email)
            ->where('status', 'active')
            ->first();
        $payments = $contract
            ? RentPayment::where('contract_id', $contract->id)->latest()->get()
            : collect();

        return view('pages.tenant.payments', compact('contract', 'payments'));
    }

    public function payRentPayment(RentPayment $rentPayment): RedirectResponse
    {
        $this->authorizeRentPayment($rentPayment);

        if ($rentPayment->isPaid()) {
            return to_route('tenant.payments')->with('info', 'Ce loyer est déjà payé.');
        }

        // The unified deposit form handles the payment (check-status flow).
        return to_route('transactions.deposit', ['rent_payment' => $rentPayment->id]);
    }

    private function authorizeRentPayment(RentPayment $rentPayment): void
    {
        abort_unless($rentPayment->contract?->tenant_email === auth()->user()->email, 403);
        abort_unless($rentPayment->contract?->status === 'active', 403);
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

        // Récupérer les données de branding
        $brandingData = BrandingHelper::getEncodedImages();

        $pdf = Pdf::loadView('pages.owner.pdf.lease-contract', array_merge(
            compact('contract', 'property'),
            $brandingData
        ));
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
