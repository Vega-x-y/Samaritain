<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Models\Chantier;
use App\Models\Client;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\Message;
use App\Models\User;
use App\Services\DocumentPdfGenerator;
use App\Services\PdfSignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        $documents = Document::query()
            ->whereHas('client', fn ($q) => $q->where('artisan_id', $artisan->id))
            ->with('client')
            ->when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20);

        $clients = Client::where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $chantiers = Chantier::where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $types = Document::TYPES;

        $documentCounts = Document::whereHas('client', fn ($q) => $q->where('artisan_id', $artisan->id))
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return view('pages.artisan.documents.index', compact('documents', 'clients', 'chantiers', 'types', 'documentCounts'));
    }

    public function create(Request $request, ?string $type = 'devis'): View
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403, 'Vous devez avoir un profil artisan.');

        if (! in_array($type, ['devis', 'facture', 'compte_rendu', 'attestation'])) {
            abort(404, 'Type de document invalide.');
        }

        $clients = Client::where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $chantiers = Chantier::where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $view = match ($type) {
            'devis' => 'pages.artisan.documents.create.devis',
            'facture' => 'pages.artisan.documents.create.facture',
            'compte_rendu' => 'pages.artisan.documents.create.compte_rendu',
            'attestation' => 'pages.artisan.documents.create.attestation',
            default => 'pages.artisan.documents.create.devis',
        };

        return view($view, compact('clients', 'chantiers'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        abort_unless($artisan, 403);

        $type = $request->input('type');

        $rules = [
            'client_id' => ['required', 'exists:clients,id'],
            'chantier_id' => ['nullable', 'exists:artisan_chantiers,id'],
            'nom' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(Document::TYPES))],
        ];

        $messages = [
            'nom.required' => 'Le nom du document est requis.',
            'client_id.required' => 'Le client est requis.',
        ];

        if ($type === 'devis') {
            $rules['reference_devis'] = ['nullable', 'string', 'max:100'];
            $rules['date_emission_devis'] = ['nullable', 'date'];
            $rules['lignes'] = ['nullable', 'array'];
            $rules['lignes.*.libelle'] = ['required_with:lignes', 'string', 'max:500'];
            $rules['lignes.*.quantite'] = ['required_with:lignes', 'numeric', 'min:1'];
            $rules['lignes.*.prix_unitaire'] = ['required_with:lignes', 'numeric', 'min:0'];
            $rules['conditions_image'] = ['nullable', 'image', 'max:5120'];
        }

        if ($type === 'facture') {
            $rules['fichier'] = ['nullable', 'file', 'max:10240'];
            $rules['numero_facture'] = ['nullable', 'string', 'max:100'];
            $rules['date_emission_facture'] = ['nullable', 'date'];
            $rules['montant_ht'] = ['nullable', 'numeric', 'min:0'];
            $rules['tva'] = ['nullable', 'numeric', 'min:0'];
            $rules['montant_ttc'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($type === 'compte_rendu') {
            $rules['fichier'] = ['nullable', 'file', 'max:10240'];
            $rules['titre_compte_rendu'] = ['nullable', 'string', 'max:255'];
            $rules['description_compte_rendu'] = ['nullable', 'string', 'max:5000'];
            $rules['date_intervention'] = ['nullable', 'date'];
            $rules['duree'] = ['nullable', 'numeric', 'min:0.5'];
            $rules['photos_avant'] = ['nullable', 'image', 'max:5120'];
            $rules['photos_apres'] = ['nullable', 'image', 'max:5120'];
        }

        if ($type === 'attestation') {
            $rules['fichier'] = ['nullable', 'file', 'max:10240'];
            $rules['reference_attestation'] = ['nullable', 'string', 'max:100'];
            $rules['titre_attestation'] = ['nullable', 'string', 'max:255'];
            $rules['description_attestation'] = ['nullable', 'string', 'max:5000'];
            $rules['date_emission_attestation'] = ['nullable', 'date'];
        }

        $validated = $request->validate($rules, $messages);

        // Vérifier que le client appartient à l'artisan
        Client::where('artisan_id', $artisan->id)->findOrFail($validated['client_id']);

        // Pour les devis, pas de fichier uploadé, on génèrera le PDF après
        if ($type === 'devis') {
            $path = null;
        } elseif ($request->hasFile('fichier')) {
            // Pour les autres types, uploader le fichier si fourni
            $file = $request->file('fichier');
            $path = $file->store('documents', 'r2');
        } else {
            $path = null;
        }

        // Construire les métadonnées selon le type
        $metadata = $this->buildMetadata($request);

        // Stocker les images supplémentaires
        if ($type === 'devis' && $request->hasFile('conditions_image')) {
            $metadata['conditions_image_path'] = $request->file('conditions_image')->store('documents/conditions', 'r2');
        }

        if ($type === 'compte_rendu') {
            if ($request->hasFile('photos_avant')) {
                $metadata['photos_avant_paths'] = [$request->file('photos_avant')->store('documents/compte_rendu', 'r2')];
            }
            if ($request->hasFile('photos_apres')) {
                $metadata['photos_apres_paths'] = [$request->file('photos_apres')->store('documents/compte_rendu', 'r2')];
            }
        }

        // Créer le document
        $document = Document::create([
            'client_id' => $validated['client_id'],
            'chantier_id' => $validated['chantier_id'] ?? null,
            'nom' => $validated['nom'],
            'path' => 'temp', // Sera mis à jour après génération PDF
            'type' => $type,
            'mime_type' => 'application/pdf', // Sera mis à jour
            'size' => 0, // Sera mis à jour
            'date_modification' => now(),
            'metadata' => $metadata,
            'status' => Document::STATUS_DRAFT,
        ]);

        // Générer le PDF automatiquement pour tous les types de documents
        try {
            $pdfGenerator = new DocumentPdfGenerator;
            $pdfGenerator->generate($document);
        } catch (\Exception $e) {
            // En cas d'erreur, supprimer le document et lever l'exception
            $document->delete();
            \Log::error('Erreur lors de la génération du PDF: '.$e->getMessage());
            throw $e;
        }

        return to_route('artisan.documents.index')
            ->with('success', 'Document créé avec succès.');
    }

    protected function buildMetadata(Request $request): array
    {
        $type = $request->input('type');
        $metadata = [];

        if ($type === 'devis') {
            $lignes = $request->input('lignes');
            if (is_array($lignes)) {
                $lignes = array_values(array_filter($lignes, fn ($l) => is_array($l) && $l !== []));
            }

            $metadata = [
                'reference' => $request->input('reference_devis'),
                'date_emission' => $request->input('date_emission_devis'),
                'lignes' => $lignes ?? [],
                'conditions_generales' => $request->input('conditions_generales', ''),
            ];
        }

        if ($type === 'facture') {
            $metadata = [
                'numero' => $request->input('numero_facture'),
                'date_emission' => $request->input('date_emission_facture'),
                'montant_ht' => $request->filled('montant_ht') ? (float) $request->input('montant_ht') : null,
                'tva' => $request->filled('tva') ? (float) $request->input('tva') : null,
                'montant_ttc' => $request->filled('montant_ttc') ? (float) $request->input('montant_ttc') : null,
            ];
        }

        if ($type === 'compte_rendu') {
            $metadata = [
                'titre' => $request->input('titre_compte_rendu'),
                'description' => $request->input('description_compte_rendu'),
                'date_intervention' => $request->input('date_intervention'),
                'duree' => $request->filled('duree') ? (float) $request->input('duree') : null,
            ];
        }

        if ($type === 'attestation') {
            $metadata = [
                'reference' => $request->input('reference_attestation'),
                'titre' => $request->input('titre_attestation'),
                'description' => $request->input('description_attestation'),
                'date_emission' => $request->input('date_emission_attestation'),
            ];
        }

        return $metadata;
    }

    public function show(Request $request, Document $document): View|RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        // Pour les devis, afficher la vue détaillée avec la signature
        if ($document->isDevis()) {
            return view('pages.artisan.documents.show', compact('document'));
        }

        // Pour les autres types de documents, rediriger vers le fichier
        return redirect($document->url);
    }

    public function edit(Request $request, Document $document): View
    {
        $this->authorizeDocument($request, $document);

        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        $clients = Client::where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $chantiers = Chantier::where('artisan_id', $artisan->id)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        $types = Document::TYPES;

        return view('pages.artisan.documents.edit', compact('document', 'clients', 'chantiers', 'types'));
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $artisan = $request->user()->artisan;

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'exists:clients,id'],
            'chantier_id' => ['nullable', 'exists:artisan_chantiers,id'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(Document::TYPES))],
        ]);

        Client::where('artisan_id', $artisan->id)->findOrFail($validated['client_id']);

        $document->update([
            'nom' => $validated['nom'],
            'client_id' => $validated['client_id'],
            'chantier_id' => $validated['chantier_id'] ?? null,
            'type' => $validated['type'],
            'date_modification' => now(),
        ]);

        return to_route('artisan.documents.index')
            ->with('success', 'Document mis à jour.');
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        Storage::disk('r2')->delete($document->path);

        $document->delete();

        return back()->with('success', 'Document supprimé avec succès.');
    }

    public function sendToClient(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $artisan = $request->user()->artisan;

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
        ]);

        $client = Client::where('artisan_id', $artisan->id)->findOrFail($validated['client_id']);

        $document->update([
            'client_id' => $validated['client_id'],
            'date_modification' => now(),
            'status' => Document::STATUS_SENT,
        ]);

        // Créer ou récupérer la conversation avec ce client
        $conversation = Conversation::firstOrCreate(
            [
                'artisan_id' => $artisan->id,
                'client_id' => $client->id,
            ],
            [
                'sujet' => 'Devis - '.$document->nom,
                'lu' => true,
                'dernier_message_at' => now(),
            ]
        );

        // Créer un message dans la conversation avec le lien du devis
        $conversation->messages()->create([
            'expediteur_type' => 'artisan',
            'expediteur_id' => $artisan->id,
            'contenu' => 'Nouveau devis à consulter',
            'lu' => false,
            'fichier_path' => $document->path,
            'fichier_nom' => $document->nom,
            'fichier_mime' => $document->mime_type,
            'fichier_taille' => $document->size,
            'document_id' => $document->id,
        ]);

        $conversation->update([
            'dernier_message_at' => now(),
            'lu' => false,
        ]);

        return back()->with('success', 'Devis envoyé au client dans sa messagerie. Le client devra cocher la case d\'attestation et renvoyer le devis avant que vous puissiez l\'exporter.');
    }

    public function exportPdf(Request $request, Document $document): mixed
    {
        $this->authorizeDocument($request, $document);

        if (! $document->canExport()) {
            return back()->with('error', 'Ce devis doit être accepté par le client avant d\'être exporté.');
        }

        // Si c'est un devis signé avec une signature réelle, générer un PDF avec la signature intégrée
        if ($document->isDevis() && $document->isSigned() && ($document->signature_data['signature'] ?? null)) {
            $pdfService = app(PdfSignatureService::class);

            return $pdfService->generateSignedPdf($document);
        }

        // Pour les autres documents, télécharger le fichier original
        if ($document->mime_type === 'application/pdf') {
            return Storage::disk('r2')->download($document->path, $document->nom.'.pdf');
        }

        return redirect($document->url);
    }

    private function authorizeDocument(Request $request, Document $document): void
    {
        $artisan = $request->user()->artisan;

        if ($document->client && $document->client->artisan_id !== $artisan?->id) {
            abort(403);
        }
    }
}
