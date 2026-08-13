<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\ChantierStatus;
use App\Http\Controllers\Controller;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChantierController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $chantiers = Chantier::query()
            ->where('artisan_id', $artisan->id)
            ->with('client')
            ->when($request->filled('statut'), fn ($q) => $q->where('statut', $request->statut))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($sub) use ($request) {
                $sub->where('nom', 'like', '%'.$request->search.'%')
                    ->orWhere('type', 'like', '%'.$request->search.'%')
                    ->orWhereHas('client', fn ($sq) => $sq->where('nom', 'like', '%'.$request->search.'%'));
            }))
            ->latest()
            ->paginate(12);

        $stats = [
            'total' => Chantier::where('artisan_id', $artisan->id)->count(),
            'en_cours' => Chantier::where('artisan_id', $artisan->id)->where('statut', ChantierStatus::EN_COURS)->count(),
            'attente' => Chantier::where('artisan_id', $artisan->id)->where('statut', ChantierStatus::ATTENTE)->count(),
            'termine' => Chantier::where('artisan_id', $artisan->id)->where('statut', ChantierStatus::TERMINE)->count(),
            'arret' => Chantier::where('artisan_id', $artisan->id)->where('statut', ChantierStatus::ARRET)->count(),
        ];

        $types = [
            'plomberie' => 'Plomberie',
            'electricite' => 'Électricité',
            'peinture' => 'Peinture',
            'maconnerie' => 'Maçonnerie',
            'menuiserie' => 'Menuiserie',
        ];

        $statuts = ChantierStatus::cases();

        $clients = Client::query()
            ->where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get();

        return view('pages.artisan.chantiers.index', compact(
            'artisan',
            'chantiers',
            'stats',
            'types',
            'statuts',
            'clients'
        ));
    }

    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $types = [
            'plomberie' => 'Plomberie',
            'electricite' => 'Électricité',
            'peinture' => 'Peinture',
            'maconnerie' => 'Maçonnerie',
            'menuiserie' => 'Menuiserie',
        ];

        $statuts = ChantierStatus::cases();

        $clients = Client::query()
            ->where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get();

        return view('pages.artisan.chantiers.create', compact('artisan', 'types', 'statuts', 'clients'));
    }

    public function edit(Request $request, Chantier $chantier): View
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $types = [
            'plomberie' => 'Plomberie',
            'electricite' => 'Électricité',
            'peinture' => 'Peinture',
            'maconnerie' => 'Maçonnerie',
            'menuiserie' => 'Menuiserie',
        ];

        $statuts = ChantierStatus::cases();

        $clients = Client::query()
            ->where('artisan_id', $chantier->artisan_id)
            ->orderBy('nom')
            ->get();

        return view('pages.artisan.chantiers.edit', compact('chantier', 'types', 'statuts', 'clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'client_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'string', 'in:plomberie,electricite,peinture,maconnerie,menuiserie'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'priorite' => ['nullable', 'string', 'in:haute,moyenne,basse'],
            'materiel' => ['nullable', 'string', 'max:5000'],
            'note_client' => ['nullable', 'string', 'max:5000'],
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['required', 'string', 'max:255'],
        ]);

        $checklist = [];
        if (! empty($validated['checklist'])) {
            foreach ($validated['checklist'] as $item) {
                if (trim($item)) {
                    $checklist[] = ['label' => trim($item), 'done' => false];
                }
            }
        }

        $chantier = $artisan->chantiers()->create([
            'client_id' => $validated['client_id'] ?? null,
            'nom' => $validated['nom'],
            'type' => $validated['type'],
            'statut' => ChantierStatus::DEVIS,
            'budget' => $validated['budget'] ?? null,
            'date_debut' => $validated['date_debut'] ?? null,
            'date_fin' => $validated['date_fin'] ?? null,
            'priorite' => $validated['priorite'] ?? null,
            'materiel' => $validated['materiel'] ?? null,
            'note_client' => $validated['note_client'] ?? null,
            'checklist' => $checklist ?: null,
            'messages' => null,
            'photos' => null,
            'devis_lines' => [
                ['label' => 'Prestation principale', 'qty' => 1, 'price' => ($validated['budget'] ?? 0) * 0.7],
                ['label' => 'Finition', 'qty' => 1, 'price' => ($validated['budget'] ?? 0) * 0.3],
            ],
            'acompte_paye' => false,
            'solde_paye' => false,
            'reception_validee' => false,
        ]);

        return to_route('artisan.chantiers.index')
            ->with('success', 'Chantier « '.$chantier->nom.' » créé avec succès.');
    }

    public function show(Request $request, Chantier $chantier): View
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $chantier->load('client');

        $types = [
            'plomberie' => 'Plomberie',
            'electricite' => 'Électricité',
            'peinture' => 'Peinture',
            'maconnerie' => 'Maçonnerie',
            'menuiserie' => 'Menuiserie',
        ];

        return view('pages.artisan.chantiers.show', compact('chantier', 'types'));
    }

    public function update(Request $request, Chantier $chantier): RedirectResponse
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'client_id' => ['nullable', 'exists:users,id'],
            'type' => ['sometimes', 'string', 'in:plomberie,electricite,peinture,maconnerie,menuiserie'],
            'statut' => ['sometimes', 'string', 'in:'.implode(',', array_map(fn ($s) => $s->value, ChantierStatus::cases()))],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'priorite' => ['nullable', 'string', 'in:haute,moyenne,basse'],
            'materiel' => ['nullable', 'string', 'max:5000'],
            'note_client' => ['nullable', 'string', 'max:5000'],
            'acompte_paye' => ['nullable', 'boolean'],
            'solde_paye' => ['nullable', 'boolean'],
            'reception_validee' => ['nullable', 'boolean'],
        ]);

        // Si un statut est fourni, le cast automatique s'en charge
        $chantier->update($validated);

        return to_route('artisan.chantiers.index')
            ->with('reload_kpis', true)
            ->with('success', 'Chantier mis à jour.');
    }

    public function updateStatut(Request $request, Chantier $chantier): RedirectResponse
    {

        $validated = $request->validate([
            'statut' => ['required', 'string', 'in:'.implode(',', array_map(fn ($s) => $s->value, ChantierStatus::cases()))],
        ]);

        $chantier->update([
            'statut' => $validated['statut'],
        ]);

        return to_route('artisan.chantiers.index')
            ->with('reload_kpis', true)->with('success', 'Statut du chantier « '.$chantier->nom.' » mis à jour.');
    }

    public function destroy(Request $request, Chantier $chantier): RedirectResponse
    {
        if ($chantier->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $chantier->delete();

        return to_route('artisan.chantiers.index')
            ->with('success', 'Chantier supprimé.');
    }
}
