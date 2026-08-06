<?php

namespace App\Services;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentPdfGenerator
{
    /**
     * Générer un PDF pour un document (devis, facture, compte rendu).
     *
     * @return string Le chemin du fichier généré
     */
    public function generate(Document $document): string
    {
        $metadata = $document->metadata;
        $client = $document->client;
        $artisan = $client->artisan;

        \Log::info('Début génération PDF document', [
            'document_id' => $document->id,
            'type' => $document->type,
            'client_id' => $client->id ?? null,
            'artisan_id' => $artisan->id ?? null,
            'metadata' => $metadata,
        ]);

        // Encoder les images en base64 (plus fiable que public_path() avec DomPDF)
        $logoBase64 = $this->encodeImage(public_path('images/logo-samaritain.png'));
        $waveBase64 = $this->encodeImage(public_path('images/header-wave.png'));

        if (! $logoBase64) {
            \Log::warning('Logo introuvable pour le PDF', ['path' => public_path('images/logo-samaritain.png')]);
        }
        if (! $waveBase64) {
            \Log::warning('Image de vague introuvable pour le PDF', ['path' => public_path('images/header-wave.png')]);
        }

        // Encoder les photos avant/après pour les comptes rendus
        $photosAvantBase64 = [];
        $photosApresBase64 = [];

        if ($document->type === Document::TYPE_COMPTE_RENDU) {
            foreach ($metadata['photos_avant_paths'] ?? [] as $photoPath) {
                $photosAvantBase64[] = $this->encodeStorageImage($photoPath);
            }
            foreach ($metadata['photos_apres_paths'] ?? [] as $photoPath) {
                $photosApresBase64[] = $this->encodeStorageImage($photoPath);
            }
        }

        // Sélectionner le template selon le type
        $template = match ($document->type) {
            Document::TYPE_DEVIS => 'pdf.devis-template',
            Document::TYPE_FACTURE => 'pdf.facture-template',
            Document::TYPE_COMPTE_RENDU => 'pdf.compte-rendu-template',
            Document::TYPE_ATTESTATION => 'pdf.attestation-template',
            default => throw new \Exception('Type de document non supporté pour la génération PDF: '.$document->type),
        };

        // Calculer les variables spécifiques au devis
        $lignes = [];
        $grandTotal = 0;

        if ($document->type === Document::TYPE_DEVIS) {
            $lignes = $metadata['lignes'] ?? [];
            foreach ($lignes as $ligne) {
                $grandTotal += ($ligne['quantite'] ?? 0) * ($ligne['prix_unitaire'] ?? 0);
            }
        }

        // Créer la vue HTML pour le PDF
        try {
            $html = view($template, compact(
                'document',
                'metadata',
                'client',
                'artisan',
                'logoBase64',
                'waveBase64',
                'photosAvantBase64',
                'photosApresBase64',
                'lignes',
                'grandTotal'
            ))->render();

            \Log::info('Template PDF rendu', [
                'document_id' => $document->id,
                'template' => $template,
                'html_length' => strlen($html),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur rendu template PDF: '.$e->getMessage(), [
                'document_id' => $document->id,
            ]);
            throw new \Exception('Erreur lors du rendu du template PDF: '.$e->getMessage());
        }

        // Générer le PDF
        try {
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            $pdfContent = $pdf->output();

            \Log::info('PDF généré', [
                'document_id' => $document->id,
                'pdf_size' => strlen($pdfContent),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur génération PDF: '.$e->getMessage(), [
                'document_id' => $document->id,
            ]);
            throw new \Exception('Erreur lors de la génération du PDF: '.$e->getMessage());
        }

        // Chemin R2 selon le type
        $prefix = match ($document->type) {
            Document::TYPE_DEVIS => 'devis',
            Document::TYPE_FACTURE => 'facture',
            Document::TYPE_COMPTE_RENDU => 'compte_rendu',
            Document::TYPE_ATTESTATION => 'attestation',
            default => 'document',
        };
        $r2Path = 'documents/'.$prefix.'_'.$document->id.'_'.time().'.pdf';

        try {
            // Upload du PDF vers R2
            $uploaded = Storage::disk('r2')->put($r2Path, $pdfContent);

            // Vérifier que le fichier a bien été uploadé (évite un appel réseau redondant)
            if (! $uploaded) {
                throw new \Exception('Le fichier PDF n\'a pas pu être uploadé vers R2');
            }

            \Log::info('PDF uploadé avec succès vers R2', [
                'document_id' => $document->id,
                'path' => $r2Path,
                'size' => strlen($pdfContent),
            ]);

            // Mettre à jour le document avec le chemin du PDF
            $document->update([
                'path' => $r2Path,
                'mime_type' => 'application/pdf',
                'size' => strlen($pdfContent),
            ]);

            return $r2Path;
        } catch (\Exception $e) {
            \Log::error('Erreur upload PDF vers R2: '.$e->getMessage(), [
                'document_id' => $document->id,
                'r2_path' => $r2Path,
                'error' => $e->getTraceAsString(),
            ]);
            throw new \Exception('Erreur lors de l\'upload du PDF vers R2: '.$e->getMessage());
        }
    }

    /**
     * Encoder une image locale en base64.
     */
    private function encodeImage(string $path): ?string
    {
        if (! file_exists($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode(file_get_contents($path));
    }

    /**
     * Encoder une image stockée sur R2 en base64.
     */
    private function encodeStorageImage(string $path): ?string
    {
        try {
            $content = Storage::disk('r2')->get($path);

            if (! $content) {
                return null;
            }

            return 'data:image/png;base64,'.base64_encode($content);
        } catch (\Exception $e) {
            \Log::warning('Impossible de charger l\'image depuis R2', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
