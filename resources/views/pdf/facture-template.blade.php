<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $document->nom }} - {{ $metadata['numero'] ?? '' }}</title>
    @include('pdf.partials.styles', ['accentColor' => '#f47920', 'accentBgColor' => '#fff8f0'])
</head>

<body>
    <div class="container">

        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Facture n° ' . ($metadata['numero'] ?? '—'),
            'waveBase64' => $waveBase64,
            'logoBase64' => $logoBase64
        ])

        <!-- Bloc client -->
        @include('pdf.partials.client-info', ['client' => $client, 'artisan' => $artisan])

        <!-- Informations de la facture -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Description</th>
                        <th class="text-right" style="width: 25%;">Montant</th>
                        <th class="text-right" style="width: 25%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $document->nom }}</td>
                        <td class="text-right">{{ number_format($metadata['montant_ht'] ?? 0, 0, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ number_format($metadata['montant_ht'] ?? 0, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sous-total / TVA -->
        <div class="totals-wrapper">
            <table class="totals-table">
                <tr>
                    <td class="totals-label">Montant HT</td>
                    <td class="totals-value">{{ number_format($metadata['montant_ht'] ?? 0, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td class="totals-label">TVA ({{ $metadata['tva'] ?? 0 }}%)</td>
                    <td class="totals-value">{{ number_format((($metadata['montant_ht'] ?? 0) * ($metadata['tva'] ?? 0)) / 100, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>

        <!-- Bandeau Total -->
        <div class="total-band flex justify-between items-center">
            <span class="total-label">Total TTC: </span>
            <span class="total-value">{{ number_format($metadata['montant_ttc'] ?? 0, 0, ',', ' ') }} FCFA</span>
        </div>

        <!-- Paiement + Signature -->
        <div class="bottom-section">
            <div class="payment-info">
                <h4>Informations de paiement</h4>
                <p>Paiement par virement bancaire</p>
                <p>Compte : {{ $artisan->compte_bancaire ?? '—' }}</p>

                <div class="terms">
                    <h4>Date d'émission</h4>
                    <p>{{ $metadata['date_emission'] ?? '—' }}</p>
                </div>
            </div>

        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer', ['message' => 'Merci de votre confiance.'])
    </div>
</body>

</html>