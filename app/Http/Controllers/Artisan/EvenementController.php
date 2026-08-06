<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\EvenementType;
use App\Http\Controllers\Controller;
use App\Models\Chantier;
use App\Models\Evenement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvenementController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $evenements = Evenement::query()
            ->where('artisan_id', $artisan->id)
            ->with('chantier')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('chantier_id'), fn ($q) => $q->where('chantier_id', $request->chantier_id))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('date_debut', $request->date))
            ->orderBy('date_debut')
            ->get();

        $types = EvenementType::cases();
        $chantiers = Chantier::where('artisan_id', $artisan->id)->get();

        return view('pages.artisan.planning.index', compact('artisan', 'evenements', 'types', 'chantiers'));
    }

    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $types = EvenementType::cases();
        $chantiers = Chantier::where('artisan_id', $artisan->id)->get();

        return view('pages.artisan.planning.create', compact('artisan', 'types', 'chantiers'));
    }

    public function edit(Request $request, Evenement $evenement): View
    {
        if ($evenement->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $types = EvenementType::cases();
        $chantiers = Chantier::where('artisan_id', $evenement->artisan_id)->get();

        return view('pages.artisan.planning.edit', compact('evenement', 'types', 'chantiers'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'chantier_id' => ['nullable', 'exists:artisan_chantiers,id'],
            'date_debut' => ['required', 'date_format:Y-m-d\TH:i'],
            'date_fin' => ['required', 'date_format:Y-m-d\TH:i', 'after:date_debut'],
            'type' => ['required', 'string', 'in:intervention,reunion,deplacement'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $evenement = $artisan->evenements()->create($validated);

        return to_route('artisan.planning.index')
            ->with('success', 'Événement « '.$evenement->titre.' » créé avec succès.');
    }

    public function show(Request $request, Evenement $evenement): View
    {
        if ($evenement->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $evenement->load('chantier');

        return view('pages.artisan.planning.show', compact('evenement'));
    }

    public function update(Request $request, Evenement $evenement): RedirectResponse
    {
        if ($evenement->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'titre' => ['sometimes', 'string', 'max:255'],
            'chantier_id' => ['nullable', 'exists:artisan_chantiers,id'],
            'date_debut' => ['sometimes', 'date_format:Y-m-d\TH:i'],
            'date_fin' => ['sometimes', 'date_format:Y-m-d\TH:i', 'after:date_debut'],
            'type' => ['sometimes', 'string', 'in:intervention,reunion,deplacement'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $evenement->update($validated);

        return to_route('artisan.planning.index')
            ->with('success', 'Événement mis à jour.');
    }

    public function destroy(Request $request, Evenement $evenement): RedirectResponse
    {
        if ($evenement->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $evenement->delete();

        return to_route('artisan.planning.index')
            ->with('success', 'Événement supprimé.');
    }
}
