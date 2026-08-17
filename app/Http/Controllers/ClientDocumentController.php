<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Notifications\DevisSigned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDocumentController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $clientIds = $user->clients()->pluck('id');

        $documents = Document::query()
            ->whereIn('client_id', $clientIds)
            ->with('client')
            ->latest()
            ->paginate(20);

        return view('pages.client.documents.index', compact('documents'));
    }

    public function show(Request $request, Document $document): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $clientIds = $user->clients()->pluck('id');

        if (! $clientIds->contains($document->client_id)) {
            abort(403, 'Ce document ne vous est pas adressé.');
        }

        if ($document->isDevis()) {
            return view('pages.client.documents.devis', compact('document'));
        }

        // Pour les factures et comptes rendus, afficher une vue dédiée avec aperçu et téléchargement
        return view('pages.client.documents.document', compact('document'));
    }

    public function returnDevis(Request $request, Document $document): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $clientIds = $user->clients()->pluck('id');

        if (! $clientIds->contains($document->client_id)) {
            abort(403, 'Ce document ne vous est pas adressé.');
        }

        if (! $document->isDevis()) {
            abort(403, 'Seuls les devis peuvent être renvoyés.');
        }

        if ($document->isSigned()) {
            return back()->with('info', 'Ce devis a déjà été accepté.');
        }

        $validated = $request->validate([
            'attestation' => ['accepted'],
        ], [
            'attestation.accepted' => 'Vous devez cocher la case pour attester votre accord.',
        ]);

        // Marquer le devis comme accepté (renvoyé sans signature)
        $document->update([
            'status' => Document::STATUS_SIGNED,
            'signed_at' => now(),
            'signature_data' => [
                'signature' => null,
                'signed_by_user_id' => $user->id,
                'signed_by_client_id' => $document->client_id,
                'signed_at' => now()->toIso8601String(),
                'returned_without_signature' => true,
            ],
        ]);

        // Notifier l'artisan que le devis a été renvoyé
        $artisan = $document->client->artisan;
        if ($artisan && $artisan->user) {
            $artisan->user->notify(new DevisSigned($document, $document->client));
        }

        return back()->with('success', 'Devis renvoyé avec succès ! L\'artisan peut maintenant l\'exporter.');
    }
}
