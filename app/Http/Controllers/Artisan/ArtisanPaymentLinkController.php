<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\ArtisanRequest;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArtisanPaymentLinkController extends Controller
{
    /**
     * Create an ArtisanRequest payment link and post it as a message in the conversation.
     */
    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);
        abort_unless($conversation->artisan_id === $artisan->id, 403);

        $validated = $request->validate([
            'total_amount' => ['required', 'integer', 'min:1'],
            'down_payment_amount' => ['required', 'integer', 'min:1', 'lte:total_amount'],
        ]);

        // The client linked to this conversation
        $client = $conversation->client;
        abort_unless($client && $client->user_id, 422, 'Le client n\'est pas lié à un compte utilisateur.');

        // Create (or reuse unpaid) ArtisanRequest for this conversation's client
        $artisanRequest = ArtisanRequest::create([
            'artisan_id' => $artisan->id,
            'user_id' => $client->user_id,
            'type' => 'paiement',
            'statut' => 'acceptee',
            'payment_status' => 'UNPAID',
            'total_amount' => $validated['total_amount'],
            'down_payment_amount' => $validated['down_payment_amount'],
            'message' => 'Lien de paiement envoyé via la messagerie.',
        ]);

        $depositUrl = route('transactions.deposit', ['artisan_request' => $artisanRequest->id]);

        // Post the payment link as a special message in the conversation
        Message::create([
            'conversation_id' => $conversation->id,
            'expediteur_type' => 'artisan',
            'expediteur_id' => $artisan->id,
            'expediteur_nom' => $artisan->nom ?? $user->name,
            'contenu' => null,
            'type' => 'payment_link',
            'metadata' => [
                'artisan_request_id' => $artisanRequest->id,
                'total_amount' => $validated['total_amount'],
                'down_payment_amount' => $validated['down_payment_amount'],
                'deposit_url' => $depositUrl,
            ],
            'lu' => false,
        ]);

        return redirect()
            ->route('artisan.messagerie.conversation', $conversation)
            ->with('success', 'Lien de paiement envoyé au client.');
    }
}
