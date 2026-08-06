<?php

namespace App\Services;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfSignatureService
{
    /**
     * Générer un PDF avec la signature intégrée.
     *
     * @return StreamedResponse
     */
    public function generateSignedPdf(Document $document)
    {
        if (! $document->isSigned() || ! ($document->signature_data['signature'] ?? null)) {
            abort(403, 'Ce document ne peut pas être exporté car il n\'est pas signé.');
        }

        $signatureImage = $document->signature_data['signature'];
        $signatureDate = $document->signed_at?->format('d/m/Y H:i') ?? 'N/A';
        $clientName = $document->client->nom ?? 'Client';

        // Créer une vue HTML pour le PDF
        $html = view('pdf.signed-devis', compact('document', 'signatureImage', 'signatureDate', 'clientName'))->render();

        // Générer le PDF
        $pdf = Pdf::loadHTML($html);

        // Configurer le PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true, // Permettre les images externes (base64)
        ]);

        // Télécharger le PDF
        return $pdf->download($document->nom.'_signe.pdf');
    }

    /**
     * Générer un PDF simple avec overlay de signature.
     *
     * @return StreamedResponse
     */
    public function generateSignedPdfWithOverlay(Document $document)
    {
        if (! $document->isSigned() || ! ($document->signature_data['signature'] ?? null)) {
            abort(403, 'Ce document ne peut pas être exporté car il n\'est pas signé.');
        }

        // Pour l'instant, utiliser la méthode simple
        // Dans une version future, on pourrait utiliser une bibliothèque comme setasign/fpdi
        // pour ajouter la signature comme overlay sur le PDF original
        return $this->generateSignedPdf($document);
    }
}
