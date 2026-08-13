<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\ChantierStatus;
use App\Http\Controllers\Controller;
use App\Models\Chantier;
use App\Models\ChantierTransaction;
use App\Models\Depense;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FinancesController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $chantiers = Chantier::query()
            ->where('artisan_id', $artisan->id)
            ->with(['client', 'devis', 'factures', 'depenses', 'transactions'])
            ->when($request->filled('chantier_id'), fn ($q) => $q->where('id', $request->chantier_id))
            ->latest()
            ->paginate(20);

        $chantiersList = Chantier::where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        // ===== Agrégats du centre financier (tous les chantiers) =====
        $allChantiers = Chantier::where('artisan_id', $artisan->id)
            ->with(['factures', 'depenses', 'transactions'])
            ->when($request->filled('search'), fn ($q) => $q->where('nom', 'like', '%'.$request->search.'%'))
            ->get();

        // CA Total = somme des budgets (FCFA HT) de tous les chantiers de l'artisan
        $totalCA = round((float) $allChantiers->sum('budget'), 2);
        $totalDepenses = round((float) $allChantiers->sum('total_depenses'), 2);
        $beneficeNet = round($totalCA - $totalDepenses, 2);
        $marge = $totalCA > 0 ? (int) round(($beneficeNet / $totalCA) * 100) : 0;
        $totalBudget = $totalCA;

        $chantierIds = $allChantiers->pluck('id');

        // Acomptes reçus : somme des acomptes déjà encaissés (type = acompte, statut = recu)
        $acomptesRecus = round((float) ChantierTransaction::whereIn('chantier_id', $chantierIds)
            ->where('type', 'acompte')
            ->where('statut', 'recu')
            ->sum('montant'), 2);

        // Impayés : somme des budgets (FCFA HT) de tous les chantiers, moins les acomptes déjà encaissés
        $impayes = round(max(0.0, $totalBudget - $acomptesRecus), 2);

        // Répartition des revenus par poste (chantier), tri décroissant par montant
        $repartitionRevenus = $allChantiers
            ->filter(fn ($c) => (float) $c->total_ca > 0)
            ->map(fn ($c) => [
                'chantier' => $c,
                'nom' => $c->nom,
                'montant' => (float) $c->total_ca,
            ])
            ->sortByDesc('montant')
            ->values();

        $maxRevenu = $repartitionRevenus->isEmpty() ? 0 : (float) $repartitionRevenus->max('montant');

        // Dépenses agrégées par catégorie (type), tri décroissant
        $depensesParType = Depense::whereIn('chantier_id', $chantierIds)
            ->get()
            ->groupBy('categorie')
            ->map(fn ($groupe) => round((float) $groupe->sum('montant'), 2))
            ->reject(fn ($montant) => $montant <= 0)
            ->sortDesc();

        $dernieresDepenses = Depense::whereIn('chantier_id', $chantierIds)
            ->with('chantier')
            ->latest()
            ->take(10)
            ->get();
        // ===== Fin agrégats =====

        return view('pages.artisan.finances.index', compact(
            'chantiers',
            'chantiersList',
            'totalCA',
            'totalDepenses',
            'beneficeNet',
            'marge',
            'acomptesRecus',
            'totalBudget',
            'impayes',
            'repartitionRevenus',
            'depensesParType',
            'maxRevenu',
            'dernieresDepenses'
        ));
    }

    // ==================== KPI JSON (endpoint) ====================

    public function kpis(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $allChantiers = Chantier::where('artisan_id', $artisan->id)
            ->with(['factures', 'depenses', 'transactions'])
            ->get();

        $chantierIds = $allChantiers->pluck('id');

        // CA Total = somme des budgets (FCFA HT) de tous les chantiers de l'artisan
        $totalCA = round((float) $allChantiers->sum('budget'), 2);
        $totalDepenses = round((float) $allChantiers->sum('total_depenses'), 2);
        $beneficeNet = round($totalCA - $totalDepenses, 2);
        $marge = $totalCA > 0 ? (int) round(($beneficeNet / $totalCA) * 100) : 0;
        $totalBudget = $totalCA;

        $acomptesRecus = round((float) ChantierTransaction::whereIn('chantier_id', $chantierIds)
            ->where('type', 'acompte')
            ->where('statut', 'recu')
            ->sum('montant'), 2);

        $impayes = round(max(0.0, $totalBudget - $acomptesRecus), 2);

        return response()->json([
            'totalBudget' => $totalBudget,
            'totalCA' => $totalCA,
            'acomptesRecus' => $acomptesRecus,
            'impayes' => $impayes,
            'totalDepenses' => $totalDepenses,
            'beneficeNet' => $beneficeNet,
            'marge' => $marge,
        ]);
    }

    public function show(Request $request, Chantier $chantier): View
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $chantier->load(['client', 'devis', 'factures', 'depenses', 'transactions']);

        $rentabilite = $chantier->calculerRentabilite();
        $totalCA = $chantier->total_ca;
        $totalDepenses = $chantier->total_depenses;

        return view('pages.artisan.finances.show', compact(
            'chantier',
            'rentabilite',
            'totalCA',
            'totalDepenses'
        ));
    }

    // ==================== DEVIS ====================

    public function storeDevis(Request $request, Chantier $chantier): RedirectResponse
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'numero' => ['required', 'string', 'max:50', 'unique:devis,numero'],
            'statut' => ['required', 'string', 'in:brouillon,envoye,signe'],
            'date_envoi' => ['nullable', 'date'],
            'date_signature' => ['nullable', 'date'],
            'montant_ht' => ['nullable', 'numeric', 'min:0'],
            'tva' => ['nullable', 'numeric', 'min:0'],
            'montant_ttc' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $devis = $chantier->devis()->create($validated);

        // Si le devis est signé, mettre à jour le statut du chantier
        if ($devis->statut === 'signe') {
            $chantier->update(['statut' => ChantierStatus::EN_COURS]);
        }

        return back()->with('success', 'Devis créé avec succès.')->with('reload_kpis', true);
    }

    public function updateDevis(Request $request, Devis $devis): RedirectResponse
    {
        if ($devis->chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'statut' => ['required', 'string', 'in:brouillon,envoye,signe'],
            'date_envoi' => ['nullable', 'date'],
            'date_signature' => ['nullable', 'date'],
            'montant_ht' => ['nullable', 'numeric', 'min:0'],
            'tva' => ['nullable', 'numeric', 'min:0'],
            'montant_ttc' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $devis->update($validated);

        // Si le devis est signé, mettre à jour le statut du chantier
        if ($devis->statut === 'signe' && $devis->chantier->statut === ChantierStatus::DEVIS) {
            $devis->chantier->update(['statut' => ChantierStatus::EN_COURS]);
        }

        return back()->with('success', 'Devis mis à jour.')->with('reload_kpis', true);
    }

    // ==================== FACTURES ====================

    public function storeFacture(Request $request, Chantier $chantier): RedirectResponse
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'numero' => ['required', 'string', 'max:50', 'unique:factures,numero'],
            'montant_ht' => ['required', 'numeric', 'min:0'],
            'tva' => ['nullable', 'numeric', 'min:0'],
            'montant_ttc' => ['required', 'numeric', 'min:0'],
            'date_emission' => ['required', 'date'],
            'date_echeance' => ['nullable', 'date'],
            'statut' => ['required', 'string', 'in:brouillon,envoyee,payee,annulee'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $chantier->factures()->create($validated);

        return back()->with('success', 'Facture créée avec succès.')->with('reload_kpis', true);
    }

    public function updateFacture(Request $request, Facture $facture): RedirectResponse
    {
        if ($facture->chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'montant_ht' => ['nullable', 'numeric', 'min:0'],
            'tva' => ['nullable', 'numeric', 'min:0'],
            'montant_ttc' => ['nullable', 'numeric', 'min:0'],
            'date_emission' => ['nullable', 'date'],
            'date_echeance' => ['nullable', 'date'],
            'statut' => ['nullable', 'string', 'in:brouillon,envoyee,payee,annulee'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $facture->update($validated);

        return back()->with('success', 'Facture mise à jour.')->with('reload_kpis', true);
    }

    // ==================== DÉPENSES ====================

    public function storeDepense(Request $request, Chantier $chantier): RedirectResponse
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'categorie' => ['required', 'string', 'in:materiaux,main_oeuvre,transport,autre'],
            'montant' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'justificatif' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'description' => ['nullable', 'string', 'max:5000'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $validated;

        if ($request->hasFile('justificatif')) {
            $path = $request->file('justificatif')->store('depenses', 'public');
            $data['justificatif'] = $path;
        }

        $chantier->depenses()->create($data);

        return back()->with('success', 'Dépense ajoutée avec succès.')->with('reload_kpis', true);
    }

    public function updateDepense(Request $request, Depense $depense): RedirectResponse
    {
        if ($depense->chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'categorie' => ['nullable', 'string', 'in:materiaux,main_oeuvre,transport,autre'],
            'montant' => ['nullable', 'numeric', 'min:0'],
            'date' => ['nullable', 'date'],
            'justificatif' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'description' => ['nullable', 'string', 'max:5000'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
        ]);

        $data = $validated;

        if ($request->hasFile('justificatif')) {
            // Supprimer l'ancien fichier
            if ($depense->justificatif) {
                Storage::disk('public')->delete($depense->justificatif);
            }
            $path = $request->file('justificatif')->store('depenses', 'public');
            $data['justificatif'] = $path;
        }

        $depense->update($data);

        return back()->with('success', 'Dépense mise à jour.')->with('reload_kpis', true);
    }

    public function destroyDepense(Request $request, Depense $depense): RedirectResponse
    {
        if ($depense->chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        // Supprimer le justificatif
        if ($depense->justificatif) {
            Storage::disk('public')->delete($depense->justificatif);
        }

        $depense->delete();

        return back()->with('success', 'Dépense supprimée.')->with('reload_kpis', true);
    }

    // ==================== TRANSACTIONS ====================

    public function storeTransaction(Request $request, Chantier $chantier): RedirectResponse
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:acompte,solde,remboursement'],
            'montant' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'statut' => ['required', 'string', 'in:en_attente,recu,rembourse'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $chantier->transactions()->create($validated);

        return back()->with('success', 'Transaction ajoutée avec succès.')->with('reload_kpis', true);
    }

    public function updateTransaction(Request $request, ChantierTransaction $transaction): RedirectResponse
    {
        if ($transaction->chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:acompte,solde,remboursement'],
            'montant' => ['nullable', 'numeric', 'min:0'],
            'date' => ['nullable', 'date'],
            'statut' => ['nullable', 'string', 'in:en_attente,recu,rembourse'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $transaction->update($validated);

        return back()->with('success', 'Transaction mise à jour.')->with('reload_kpis', true);
    }
}
