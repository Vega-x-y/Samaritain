<?php

namespace App\Services;

use App\Models\Document;
use App\Support\BrandingHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DevisPdfGenerator
{
    /**
     * Générer un PDF de devis depuis les métadonnées.
     *
     * @return string Le chemin du fichier généré
     */
    public function generate(Document $document): string
    {
        $metadata = $document->metadata;
        $client = $document->client;
        $artisan = $client->artisan;

        \Log::info('Début génération PDF devis', [
            'document_id' => $document->id,
            'client_id' => $client->id ?? null,
            'artisan_id' => $artisan->id ?? null,
            'metadata' => $metadata,
        ]);

        // Calculer le total
        $grandTotal = 0;
        $lignes = $metadata['lignes'] ?? [];
        foreach ($lignes as $ligne) {
            $grandTotal += ($ligne['quantite'] ?? 0) * ($ligne['prix_unitaire'] ?? 0);
        }

        // Récupérer les données de branding
        $brandingData = BrandingHelper::getEncodedImages();
        extract($brandingData); // $logoBase64, $waveBase64

        if (! $logoBase64) {
            \Log::warning('Logo introuvable pour le PDF');
        }
        if (! $waveBase64) {
            \Log::warning('Image de vague introuvable pour le PDF');
        }

        // Créer la vue HTML pour le PDF
        try {
            $html = view('pdf.devis-template', compact(
                'document',
                'metadata',
                'client',
                'artisan',
                'lignes',
                'grandTotal',
                'logoBase64',
                'waveBase64'
            ))->render();

            \Log::info('Template PDF rendu', [
                'document_id' => $document->id,
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

        // Upload vers R2 directement depuis le PDF
        $r2Path = 'documents/devis_'.$document->id.'_'.time().'.pdf';

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
}
