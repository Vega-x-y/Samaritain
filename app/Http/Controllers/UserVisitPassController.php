<?php

namespace App\Http\Controllers;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Exceptions\PawaPayException;
use App\Http\Requests\StoreVisitPassRequest;
use App\Models\Parcelle;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\VisitPass;
use App\Services\PawapayService;
use App\Services\VisitPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserVisitPassController extends Controller
{
    public function __construct(
        protected VisitPassService $visitPassService,
        protected PawapayService $pawapay
    ) {}

    /**
     * Show the form to create a visit pass for a property.
     */
    public function create(Property|Parcelle $visitPassable)
    {
        $price = $this->visitPassService->getPassPrice();

        return view('visit-passes.create', compact('visitPassable', 'price'));
    }

    /**
     * Store the visit pass request and send the user to the dedicated payment step.
     */
    public function store(StoreVisitPassRequest $request)
    {
        $visitPass = $this->visitPassService->createVisitPass($request->validated());

        // Open PawaPay's hosted Payment Page immediately for pass purchases.
        return $this->initiatePayment($request, $visitPass);
    }

    /**
     * Open PawaPay's hosted Payment Page for an existing pass.
     */
    public function pay(VisitPass $visitPass)
    {
        Gate::authorize('view', $visitPass);

        if ($visitPass->isPaid()) {
            return redirect()->route('my-visit-passes.show', $visitPass)
                ->with('info', 'Ce pass visite est déjà payé.');
        }

        return $this->initiatePayment(request(), $visitPass);
    }

    /**
     * Redirect the customer to pawaPay's hosted payment page.
     *
     * Generate and persist the UUIDv4 depositId BEFORE calling pawaPay so it can
     * serve as the reconciliation anchor. On an HTTP failure the transaction is
     * kept as pending — never failed.
     */
    public function initiatePayment(Request $request, VisitPass $visitPass)
    {
        Gate::authorize('view', $visitPass);

        if ($visitPass->isPaid()) {
            return redirect()->route('my-visit-passes.show', $visitPass)
                ->with('info', 'Ce pass visite est déjà payé.');
        }

        $depositId = (string) Str::uuid();
        $currency = config('services.pawapay.currency', 'XAF');
        $country = config('services.pawapay.country', 'COG');

        $transaction = Transaction::create([
            'user_id' => $visitPass->user_id,
            'visit_pass_id' => $visitPass->id,
            'type' => TransactionType::DEPOSIT,
            'status' => TransactionStatus::PENDING,
            'amount' => $visitPass->amount,
            'deposit_id' => $depositId,
            'currency' => $currency,
        ]);

        $visitPass->update(['transaction_id' => $transaction->transaction_id]);

        try {
            $result = $this->pawapay->createPaymentPage(
                depositId: $depositId,
                returnUrl: route('transactions.callback', $transaction),
                amount: (string) $transaction->amount,
                currency: $transaction->currency,
                clientReferenceId: $visitPass->reference,
                country: $country,
                reason: 'Pass visite '.$visitPass->reference,
                customerMessage: 'Pass visite',
            );

            $transaction->update([
                'status' => TransactionStatus::PENDING,
                'raw_response' => $result,
            ]);

            return redirect()->away($result['redirectUrl']);
        } catch (PawaPayException $e) {
            // Do NOT mark as failed — leave as pending for reconciliation.
            $transaction->update([
                'raw_response' => ['error' => $e->getMessage(), 'status_code' => $e->statusCode],
            ]);
        }

        return redirect()->route('transactions.pending', $transaction);
    }

    /**
     * List the authenticated user's visit passes.
     */
    public function index()
    {
        Gate::authorize('viewAny', VisitPass::class);

        $visitPasses = $this->visitPassService->getUserVisitPasses();

        return view('visit-passes.index', compact('visitPasses'));
    }

    /**
     * Show a single visit pass detail.
     */
    public function show(VisitPass $visitPass)
    {
        Gate::authorize('view', $visitPass);

        $visitPass->load('visitPassable');

        return view('visit-passes.show', compact('visitPass'));
    }

    /**
     * Download the visit pass PDF.
     */
    public function download(VisitPass $visitPass)
    {
        Gate::authorize('view', $visitPass);

        if (! $visitPass->isDownloadable()) {
            return redirect()->back()
                ->with('error', 'Le pass n\'est pas encore disponible au téléchargement. Veuillez d\'abord effectuer le paiement.');
        }

        if (! $visitPass->pdf_path || ! Storage::exists($visitPass->pdf_path)) {
            $this->visitPassService->generatePdf($visitPass);
            $visitPass->refresh();
        }

        return Storage::download(
            $visitPass->pdf_path,
            'pass-visite-'.$visitPass->reference.'.pdf'
        );
    }

    /**
     * Retry payment for a failed visit pass.
     */
    public function retryPayment(VisitPass $visitPass)
    {
        Gate::authorize('retryPayment', $visitPass);

        return redirect()->route('my-visit-passes.pay', $visitPass);
    }

    /**
     * Delete the visit pass.
     */
    public function destroy(VisitPass $visitPass)
    {
        Gate::authorize('delete', $visitPass);

        if ($visitPass->qr_code_path) {
            Storage::delete($visitPass->qr_code_path);
        }

        if ($visitPass->pdf_path) {
            Storage::delete($visitPass->pdf_path);
        }

        $visitPass->delete();

        return redirect()->route('my-visit-passes.index')
            ->with('success', 'Votre pass visite a été supprimé avec succès.');
    }
}
