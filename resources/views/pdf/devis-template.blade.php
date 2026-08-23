<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $document->nom }} - {{ $metadata['reference'] ?? '' }}</title>
    @include('pdf.partials.styles', ['accentColor' => '#f47920', 'accentBgColor' => '#fff8f0'])
</head>

<body>
    <div class="container">

        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Devis n° ' . ($metadata['reference'] ?? '—'),
            'waveBase64' => $waveBase64,
            'logoBase64' => $logoBase64
        ])

        <!-- Bloc client -->
        @include('pdf.partials.client-info', ['client' => $client, 'artisan' => $artisan])

        <!-- Tableau des lignes -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Description</th>
                        <th class="text-right" style="width: 20%;">Prix unitaire</th>
                        <th class="text-center" style="width: 20%;">Quantité</th>
                        <th class="text-right" style="width: 20%;">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lignes as $ligne)
                        @php
                            $totalLigne = ($ligne['quantite'] ?? 0) * ($ligne['prix_unitaire'] ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $ligne['libelle'] ?? '' }}</td>
                            <td class="text-right">{{ number_format($ligne['prix_unitaire'] ?? 0, 0, ',', ' ') }} FCFA</td>
                            <td class="text-center">{{ $ligne['quantite'] ?? 0 }}</td>
                            <td class="text-right">{{ number_format($totalLigne, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Sous-total / TVA -->
        <div class="totals-wrapper">
            <table class="totals-table">
                <tr>
                    <td class="totals-label">Subtotal</td>
                    <td class="totals-value">{{ number_format($grandTotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td class="totals-label">TVA (0%)</td>
                    <td class="totals-value">0 FCFA</td>
                </tr>
            </table>
        </div>

        <!-- Bandeau Total -->
        <div class="total-band flex justify-between items-center">

            <span class="total-label">Total: </span>
            <span class="total-value">{{ number_format($grandTotal, 0, ',', ' ') }} FCFA</span>
        </div>

        <!-- Paiement + Signature -->
        <div class="bottom-section">
            <div class="payment-info">
                <h4>Informations de paiement</h4>
                <p>Paiement par virement bancaire</p>
                <p>Compte : {{ $artisan->compte_bancaire ?? '—' }}</p>

                <div class="terms">
                    <h4>Termes &amp; conditions</h4>
                    <p>{{ $metadata['conditions_generales'] ?? 'Ce devis est valable 30 jours à compter de sa date d\'émission.' }}
                    </p>
                </div>
            </div>

        </div>

        <!-- Pied de page -->
        <div style="padding: 15px 35px; font-style: italic; font-size: 10px; color: #666;">
            J'atteste avoir pris connaissance du devis ci-dessus et j'accepte les termes de ce devis.
            — {{ $client->nom ?? 'N/A' }}
        </div>
        @include('pdf.partials.footer', ['message' => 'Merci de retourner ce devis signé pour accord.'])
    </div>
</body>

</html>