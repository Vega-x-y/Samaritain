<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientMessagerieController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $clientIds = $user->clients()->pluck('id');

        $conversations = Conversation::whereIn('client_id', $clientIds)
            ->with(['artisan', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('dernier_message_at')
            ->get();

        $messagesNonLus = Message::whereHas('conversation', fn ($q) => $q->whereIn('client_id', $clientIds))
            ->where('lu', false)
            ->where('expediteur_type', '!=', 'client')
            ->count();

        return view('pages.client.messagerie.index', compact('conversations', 'messagesNonLus'));
    }

    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $clientIds = $user->clients()->pluck('id');

        $artisans = Artisan::whereHas('clients', fn ($q) => $q->whereIn('id', $clientIds))
            ->with('user')
            ->orderBy('business_name')
            ->get();

        return view('pages.client.messagerie.create', compact('artisans'));
    }

    public function storeConversation(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $client = $user->clients()->firstOrFail();

        $validated = $request->validate([
            'artisan_id' => ['required', 'exists:artisans,id'],
            'sujet' => ['nullable', 'string', 'max:255'],
        ]);

        $artisan = Artisan::findOrFail($validated['artisan_id']);

        $existingConversation = Conversation::where('artisan_id', $artisan->id)
            ->where('client_id', $client->id)
            ->first();

        if ($existingConversation) {
            return to_route('client.messagerie.show', $existingConversation)
                ->with('info', 'Une conversation existe déjà avec cet artisan.');
        }

        $conversation = Conversation::create([
            'artisan_id' => $artisan->id,
            'client_id' => $client->id,
            'sujet' => $validated['sujet'] ?? null,
            'lu' => false,
            'dernier_message_at' => now(),
        ]);

        return to_route('client.messagerie.show', $conversation)
            ->with('success', 'Conversation créée avec '.$artisan->business_name.'.');
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();

        if (! $user->clients()->where('id', $conversation->client_id)->exists()) {
            abort(403);
        }

        $conversation->load('artisan', 'messages');
        $conversation->messages()->where('lu', false)->where('expediteur_type', '!=', 'client')->update(['lu' => true]);

        return view('pages.client.messagerie.conversation', compact('conversation'));
    }

    public function storeMessage(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        if (! $user->clients()->where('id', $conversation->client_id)->exists()) {
            abort(403);
        }

        $validated = $request->validate([
            'contenu' => ['nullable', 'string', 'max:2000'],
            'fichier' => ['nullable', 'file', 'max:10240'],
        ]);

        $client = $user->clients()->where('id', $conversation->client_id)->firstOrFail();

        $data = [
            'expediteur_type' => 'client',
            'expediteur_id' => $client->id,
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
        $conversation->update(['dernier_message_at' => now(), 'lu' => false]);

        return back()->with('success', 'Message envoyé.');
    }

    public function destroyMessage(Request $request, Message $message): RedirectResponse
    {
        if (! $request->user()->clients()->where('id', $message->conversation->client_id)->exists()) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Message supprimé.');
    }

    public function destroyConversation(Request $request, Conversation $conversation): RedirectResponse
    {
        if (! $request->user()->clients()->where('id', $conversation->client_id)->exists()) {
            abort(403);
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return to_route('client.messagerie.index')->with('success', 'Conversation supprimée.');
    }

    public function destroyAllConversations(Request $request): RedirectResponse
    {
        $user = $request->user();
        $clientIds = $user->clients()->pluck('id');

        $conversations = Conversation::whereIn('client_id', $clientIds)->get();

        foreach ($conversations as $conversation) {
            $conversation->messages()->delete();
            $conversation->delete();
        }

        return to_route('client.messagerie.index')->with('success', 'Toutes les conversations ont été supprimées.');
    }
}
