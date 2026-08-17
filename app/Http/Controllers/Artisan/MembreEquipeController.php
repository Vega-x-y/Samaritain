<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\MembreStatut;
use App\Http\Controllers\Controller;
use App\Models\MembreEquipe;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembreEquipeController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $membres = MembreEquipe::query()
            ->where('artisan_id', $artisan->id)
            ->with('user')
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('nom', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('telephone', 'like', '%'.$request->search.'%')
                    ->orWhere('role', 'like', '%'.$request->search.'%');
            }))
            ->latest()
            ->paginate(12);

        $stats = [
            'total' => MembreEquipe::where('artisan_id', $artisan->id)->count(),
            'actif' => MembreEquipe::where('artisan_id', $artisan->id)->where('statut', MembreStatut::ACTIF)->count(),
            'inactif' => MembreEquipe::where('artisan_id', $artisan->id)->where('statut', MembreStatut::INACTIF)->count(),
        ];

        $statuts = MembreStatut::cases();

        return view('pages.artisan.equipe.index', compact('artisan', 'membres', 'stats', 'statuts'));
    }

    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $statuts = MembreStatut::cases();

        return view('pages.artisan.equipe.create', compact('artisan', 'statuts'));
    }

    public function edit(Request $request, MembreEquipe $membre): View
    {
        if ($membre->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $statuts = MembreStatut::cases();

        return view('pages.artisan.equipe.edit', compact('membre', 'statuts'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'statut' => ['required', 'string', 'in:actif,inactif'],
        ]);

        $membre = $artisan->membresEquipe()->create($validated);

        return to_route('artisan.equipe.index')
            ->with('success', 'Membre « '.$membre->nom.' » ajouté avec succès.');
    }

    public function show(Request $request, MembreEquipe $membre): View
    {
        if ($membre->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $membre->load('chantiers');

        $types = [
            'plomberie' => 'Plomberie',
            'electricite' => 'Électricité',
            'peinture' => 'Peinture',
            'maconnerie' => 'Maçonnerie',
            'menuiserie' => 'Menuiserie',
        ];

        return view('pages.artisan.equipe.show', compact('membre', 'types'));
    }

    public function update(Request $request, MembreEquipe $membre): RedirectResponse
    {
        if ($membre->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'max:100'],
            'telephone' => ['sometimes', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'statut' => ['sometimes', 'string', 'in:actif,inactif'],
        ]);

        $membre->update($validated);

        return to_route('artisan.equipe.index')
            ->with('success', 'Membre mis à jour.');
    }

    public function destroy(Request $request, MembreEquipe $membre): RedirectResponse
    {
        if ($membre->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $membre->delete();

        return to_route('artisan.equipe.index')
            ->with('success', 'Membre supprimé.');
    }
}
