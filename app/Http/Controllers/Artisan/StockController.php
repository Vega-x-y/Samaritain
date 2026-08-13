<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\ArticleStock;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $articles = ArticleStock::query()
            ->where('artisan_id', $artisan->id)
            ->when($request->filled('categorie'), fn ($q) => $q->where('categorie', $request->categorie))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('nom', 'like', '%'.$request->search.'%')
                    ->orWhere('reference', 'like', '%'.$request->search.'%')
                    ->orWhere('categorie', 'like', '%'.$request->search.'%')
                    ->orWhere('fournisseur', 'like', '%'.$request->search.'%');
            }))
            ->latest()
            ->paginate(12);

        $stockAlerte = ArticleStock::where('artisan_id', $artisan->id)
            ->whereColumn('quantite', '<=', 'seuil_alerte')
            ->count();

        $stats = [
            'total' => ArticleStock::where('artisan_id', $artisan->id)->count(),
            'stock_alerte' => $stockAlerte,
            'valeur_totale' => ArticleStock::where('artisan_id', $artisan->id)->get()->sum(fn ($a) => $a->valeur_totale),
        ];

        $categories = ArticleStock::where('artisan_id', $artisan->id)
            ->distinct()
            ->pluck('categorie')
            ->filter()
            ->values();

        return view('pages.artisan.stock.index', compact('artisan', 'articles', 'stats', 'categories'));
    }

    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        return view('pages.artisan.stock.create', compact('artisan'));
    }

    public function edit(Request $request, ArticleStock $article): View
    {
        if ($article->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        return view('pages.artisan.stock.edit', compact('article'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'quantite' => ['required', 'integer', 'min:0'],
            'seuil_alerte' => ['required', 'integer', 'min:0'],
            'prix_unitaire' => ['nullable', 'numeric', 'min:0'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
        ]);

        $article = $artisan->articlesStock()->create($validated);

        return to_route('artisan.stock.index')
            ->with('success', 'Article « '.$article->nom.' » créé avec succès.');
    }

    public function show(Request $request, ArticleStock $article): View
    {
        if ($article->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $article->load('mouvements');

        return view('pages.artisan.stock.show', compact('article'));
    }

    public function update(Request $request, ArticleStock $article): RedirectResponse
    {
        if ($article->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'quantite' => ['sometimes', 'integer', 'min:0'],
            'seuil_alerte' => ['sometimes', 'integer', 'min:0'],
            'prix_unitaire' => ['nullable', 'numeric', 'min:0'],
            'fournisseur' => ['nullable', 'string', 'max:255'],
        ]);

        $article->update($validated);

        return to_route('artisan.stock.index')
            ->with('success', 'Article mis à jour.');
    }

    public function destroy(Request $request, ArticleStock $article): RedirectResponse
    {
        if ($article->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $article->delete();

        return to_route('artisan.stock.index')
            ->with('success', 'Article supprimé.');
    }

    public function mouvement(Request $request, ArticleStock $article): RedirectResponse
    {
        if ($article->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:entree,sortie'],
            'quantite' => ['required', 'integer', 'min:1'],
            'motif' => ['nullable', 'string', 'max:500'],
        ]);

        $quantite = (int) $validated['quantite'];
        $type = $validated['type'];

        if ($type === 'sortie' && $article->quantite < $quantite) {
            return back()->with('error', 'Stock insuffisant pour cette sortie.');
        }

        $article->mouvements()->create([
            'type' => $type,
            'quantite' => $quantite,
            'motif' => $validated['motif'] ?? null,
            'date' => now(),
        ]);

        if ($type === 'entree') {
            $article->increment('quantite', $quantite);
        } else {
            $article->decrement('quantite', $quantite);
        }

        return to_route('artisan.stock.show', $article)
            ->with('success', 'Mouvement enregistré avec succès.');
    }
}
