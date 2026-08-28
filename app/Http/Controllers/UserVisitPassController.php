<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVisitPassRequest;
use App\Models\Parcelle;
use App\Models\Property;
use App\Models\VisitPass;
use App\Services\VisitPassService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class UserVisitPassController extends Controller
{
    public function __construct(
        protected VisitPassService $visitPassService,
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
     * Store the visit pass request and redirect to the deposit form of the new
     * PawaPay integration, with the pass as the payment context.
     */
    public function store(StoreVisitPassRequest $request)
    {
        $visitPass = $this->visitPassService->createVisitPass($request->validated());

        return redirect()->route('transactions.deposit', ['visit_pass' => $visitPass->uuid]);
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
