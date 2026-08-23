<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->nom }} - Signé</title>
    @include('pdf.partials.styles', ['accentColor' => '#10b981', 'accentBgColor' => '#f0fdf4'])
    <style>
        /* Styles spécifiques au devis signé */
        .info-section {
            margin-bottom: 25px;
            padding: 15px 35px;
            background: #f0fdf4;
            border-left: 4px solid #10b981;
        }

        .info-section h2 {
            font-size: 13px;
            color: #10b981;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .info-item {
            margin-bottom: 5px;
        }

        .info-item .label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
        }

        .info-item .value {
            color: #333;
            font-size: 10.5px;
        }

        .signature-section {
            margin: 30px 35px;
            padding: 20px;
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 8px;
        }

        .signature-section h2 {
            color: #10b981;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .signature-content {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        .signature-image {
            flex: 0 0 300px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
        }

        .signature-image img {
            max-width: 100%;
            max-height: 120px;
            display: block;
            margin: 0 auto;
        }

        .signature-image .label {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }

        .signature-info {
            flex: 1;
        }

        .signature-info .info-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .signature-info .info-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => $document->nom,
            'waveBase64' => $waveBase64 ?? null,
            'logoBase64' => $logoBase64 ?? null
        ])

        <!-- Badge de signature -->
        <div style="text-align: center; padding: 15px 35px;">
            <span class="status-badge">✓ DOCUMENT SIGNÉ</span>
        </div>

        <!-- Informations du document -->
        <div class="info-section">
            <h2>Informations du document</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Client :</span>
                    <span class="value">{{ $clientName }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Type :</span>
                    <span class="value">{{ $document->type_label }}</span>
                </div>
                @if($document->metadata['reference'] ?? null)
                    <div class="info-item">
                        <span class="label">Référence :</span>
                        <span class="value">{{ $document->metadata['reference'] }}</span>
                    </div>
                @endif
                @if($document->metadata['date_emission'] ?? null)
                    <div class="info-item">
                        <span class="label">Date d'émission :</span>
                        <span class="value">{{ $document->metadata['date_emission'] }}</span>
                    </div>
                @endif
                <div class="info-item">
                    <span class="label">Date de création :</span>
                    <span class="value">{{ $document->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-item">
                    <span class="label">Date de signature :</span>
                    <span class="value">{{ $signatureDate }}</span>
                </div>
            </div>
        </div>

        <!-- Détails du devis -->
        @if(isset($document->metadata['lignes']) && count($document->metadata['lignes']) > 0)
            <div class="info-section">
                <h2>Détails du devis</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th style="text-align: right;">Qté</th>
                            <th style="text-align: right;">Prix unitaire</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $grandTotal = 0; @endphp
                        @foreach($document->metadata['lignes'] as $ligne)
                            @php
                                $total = ($ligne['quantite'] ?? 0) * ($ligne['prix_unitaire'] ?? 0);
                                $grandTotal += $total;
                            @endphp
                            <tr>
                                <td>{{ $ligne['libelle'] ?? '' }}</td>
                                <td style="text-align: right;">{{ $ligne['quantite'] ?? 0 }}</td>
                                <td style="text-align: right;">{{ number_format($ligne['prix_unitaire'] ?? 0, 0, ',', ' ') }} FCFA</td>
                                <td style="text-align: right;">{{ number_format($total, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" style="text-align: right; font-weight: bold;">Total :</td>
                            <td style="text-align: right; font-weight: bold;">{{ number_format($grandTotal, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Section signature -->
        <div class="signature-section">
            <h2>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
                Signature électronique
            </h2>
            <div class="signature-content">
                <div class="signature-image">
                    <img src="{{ $signatureImage }}" alt="Signature du client">
                    <div class="label">Signature du client</div>
                </div>
                <div class="signature-info">
                    <div class="info-item">
                        <span class="label">Signé par :</span>
                        <span class="value">{{ $clientName }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Date et heure :</span>
                        <span class="value">{{ $signatureDate }}</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Statut :</span>
                        <span class="value" style="color: #10b981; font-weight: bold;">✓ Signé électroniquement</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Attestation :</span>
                        <span class="value">✓ Le client a attesté l'authenticité de sa signature</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer')
        <div style="text-align: center; padding: 5px 35px 15px; font-size: 8px; color: #999;">
            <p>Ce document a été signé électroniquement conformément aux dispositions légales en vigueur.</p>
            <p style="margin-top: 5px;">La signature électronique a la même valeur juridique qu'une signature manuscrite.</p>
        </div>
    </div>
</body>
</html>