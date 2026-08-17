<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Groupe;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessagerieController extends Controller
{
    public function createConversation(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        // Clients qui ont un user_id (liés à un utilisateur existant)
        $clients = $artisan->clients()
            ->whereNotNull('user_id')
            ->with('user')
            ->orderBy('nom')
            ->get();

        return view('pages.artisan.messagerie.create-conversation', compact('artisan', 'clients'));
    }

    public function storeConversation(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'sujet' => ['nullable', 'string', 'max:255'],
        ]);

        // Vérifier que le client appartient bien à cet artisan
        $client = $artisan->clients()->findOrFail($validated['client_id']);

        // Vérifier que le client a un user_id
        if (! $client->user_id) {
            return back()->withErrors(['client_id' => 'Ce client n\'est pas lié à un utilisateur.']);
        }

        // Vérifier qu'une conversation n'existe pas déjà
        $existingConversation = Conversation::where('artisan_id', $artisan->id)
            ->where('client_id', $client->id)
            ->first();

        if ($existingConversation) {
            return to_route('artisan.messagerie.conversation', $existingConversation)
                ->with('info', 'Une conversation existe déjà avec ce client.');
        }

        $conversation = Conversation::create([
            'artisan_id' => $artisan->id,
            'client_id' => $client->id,
            'sujet' => $validated['sujet'] ?? null,
            'lu' => true,
            'dernier_message_at' => now(),
        ]);

        return to_route('artisan.messagerie.conversation', $conversation)
            ->with('success', 'Conversation créée avec '.$client->nom.'.');
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $conversations = Conversation::where('artisan_id', $artisan->id)
            ->with(['client', 'membreEquipe', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('sujet', 'like', '%'.$request->search.'%')
                        ->orWhereHas('client', fn ($sq) => $sq->where('nom', 'like', '%'.$request->search.'%'))
                        ->orWhereHas('membreEquipe', fn ($sq) => $sq->where('nom', 'like', '%'.$request->search.'%'));
                });
            })
            ->orderByDesc('dernier_message_at')
            ->get();

        $groupes = Groupe::where('artisan_id', $artisan->id)
            ->with('messages')
            ->get();

        $messagesNonLus = Message::whereHas('conversation', fn ($q) => $q->where('artisan_id', $artisan->id))
            ->where('lu', false)
            ->where('expediteur_type', '!=', 'artisan')
            ->count();

        return view('pages.artisan.messagerie.index', compact('artisan', 'conversations', 'groupes', 'messagesNonLus'));
    }

    public function conversation(Request $request, Conversation $conversation): View
    {
        if ($conversation->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $conversation->load('messages');
        $conversation->messages()->where('lu', false)->where('expediteur_type', '!=', 'artisan')->update(['lu' => true]);

        return view('pages.artisan.messagerie.conversation', compact('conversation'));
    }

    public function storeMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        if ($conversation->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'contenu' => ['nullable', 'string', 'max:2000'],
            'fichier' => ['nullable', 'file', 'max:10240'],
        ]);

        $artisan = $request->user()->artisan;

        $data = [
            'expediteur_type' => 'artisan',
            'expediteur_id' => $artisan->id,
            'lu' => false,
        ];

        if (! empty($validated['contenu'])) {
            $data['contenu'] = $validated['contenu'];
        }

        if ($request->hasFile('fichier')) {
            $fichier = $request->file('fichier');
            $path = $fichier->store('messages/'.$conversation->id, 'r2');

            $data['fichier_path'] = $path;
            $data['fichier_nom'] = $fichier->getClientOriginalName();
            $data['fichier_mime'] = $fichier->getClientMimeType();
            $data['fichier_taille'] = $fichier->getSize();
        }

        if (empty($data['contenu']) && empty($data['fichier_path'])) {
            return back()->withErrors(['contenu' => 'Veuillez saisir un message ou joindre un fichier.']);
        }

        $conversation->messages()->create($data);

        $conversation->update([
            'dernier_message_at' => now(),
            'lu' => true,
        ]);

        return back()->with('success', 'Message envoyé.');
    }

    public function createGroupe(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        return view('pages.artisan.messagerie.groupes.create', compact('artisan'));
    }

    public function storeGroupe(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $groupe = $artisan->groupes()->create($validated);

        return to_route('artisan.messagerie.index')->with('success', 'Groupe créé.');
    }

    public function showGroupe(Request $request, Groupe $groupe): View
    {
        if ($groupe->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $groupe->load('messages');
        $groupe->messages()->where('lu', false)->where('expediteur_type', '!=', 'artisan')->update(['lu' => true]);

        return view('pages.artisan.messagerie.groupe', compact('groupe'));
    }

    public function editGroupe(Request $request, Groupe $groupe): View
    {
        if ($groupe->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        return view('pages.artisan.messagerie.groupes.edit', compact('groupe'));
    }

    public function updateGroupe(Request $request, Groupe $groupe): RedirectResponse
    {
        if ($groupe->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $groupe->update($validated);

        return to_route('artisan.messagerie.index')->with('success', 'Groupe mis à jour.');
    }

    public function destroyGroupe(Request $request, Groupe $groupe): RedirectResponse
    {
        if ($groupe->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $groupe->delete();

        return to_route('artisan.messagerie.index')->with('success', 'Groupe supprimé.');
    }

    public function storeGroupeMessage(Request $request, Groupe $groupe): RedirectResponse
    {
        if ($groupe->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'contenu' => ['required', 'string', 'max:2000'],
        ]);

        $artisan = $request->user()->artisan;

        $groupe->messages()->create([
            'expediteur_type' => 'artisan',
            'expediteur_id' => $artisan->id,
            'contenu' => $validated['contenu'],
            'lu' => true,
        ]);

        return back()->with('success', 'Message envoyé.');
    }

    public function destroyMessage(Request $request, Message $message): RedirectResponse
    {
        if ($message->conversation->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Message supprimé.');
    }

    public function destroyConversation(Request $request, Conversation $conversation): RedirectResponse
    {
        if ($conversation->artisan_id !== $request->user()->artisan?->id) {
            abort(403);
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return to_route('artisan.messagerie.index')->with('success', 'Conversation supprimée.');
    }

    public function destroyAllConversations(Request $request): RedirectResponse
    {
        $artisan = $request->user()->artisan;

        abort_unless($artisan, 403);

        $conversations = Conversation::where('artisan_id', $artisan->id)->get();

        foreach ($conversations as $conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        }

        return to_route('artisan.messagerie.index')->with('success', 'Toutes les conversations ont été supprimées.');
    }
}
