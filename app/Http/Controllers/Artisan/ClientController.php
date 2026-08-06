<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\ClientType;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $clients = Client::query()
            ->where('artisan_id', $artisan->id)
            ->with('user')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('search'), fn ($q) => $q->where('nom', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(12);

        $stats = [
            'total' => Client::where('artisan_id', $artisan->id)->count(),
            'particulier' => Client::where('artisan_id', $artisan->id)->where('type', ClientType::PARTICULIER)->count(),
            'entreprise' => Client::where('artisan_id', $artisan->id)->where('type', ClientType::ENTREPRISE)->count(),
        ];

        $types = ClientType::cases();

        return view('pages.artisan.clients.index', compact('artisan', 'clients', 'stats', 'types'));
    }

    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $types = ClientType::cases();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('pages.artisan.clients.create', compact('artisan', 'types', 'users'));
    }

    public function edit(Request $request, Client $client): View
    {
        if ($client->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $types = ClientType::cases();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'email']);

        return view('pages.artisan.clients.edit', compact('client', 'types', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'telephone' => ['required', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:particulier,entreprise'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        /** @var User $selectedUser */
        $selectedUser = User::findOrFail($validated['user_id']);

        $client = $artisan->clients()->create([
            'user_id' => $selectedUser->id,
            'nom' => $selectedUser->name,
            'email' => $selectedUser->email,
            'telephone' => $validated['telephone'],
            'adresse' => $validated['adresse'] ?? null,
            'type' => $validated['type'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return to_route('artisan.clients.index')
            ->with('success', 'Client « '.$client->nom.' » créé avec succès.');
    }

    public function show(Request $request, Client $client): View
    {
        if ($client->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $client->load('chantiers');

        $types = [
            'plomberie' => 'Plomberie',
            'electricite' => 'Électricité',
            'peinture' => 'Peinture',
            'maconnerie' => 'Maçonnerie',
            'menuiserie' => 'Menuiserie',
        ];

        return view('pages.artisan.clients.show', compact('client', 'types'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        if ($client->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['sometimes', 'exists:users,id'],
            'telephone' => ['sometimes', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:1000'],
            'type' => ['sometimes', 'string', 'in:particulier,entreprise'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $data = $validated;

        // Si un user_id est fourni, mettre à jour le nom et l'email depuis l'utilisateur
        if (isset($validated['user_id']) && $validated['user_id'] != $client->user_id) {
            /** @var User $selectedUser */
            $selectedUser = User::findOrFail($validated['user_id']);
            $data['nom'] = $selectedUser->name;
            $data['email'] = $selectedUser->email;
        }

        $client->update($data);

        return to_route('artisan.clients.index')
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        if ($client->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $client->delete();

        return to_route('artisan.clients.index')
            ->with('success', 'Client supprimé.');
    }
}
