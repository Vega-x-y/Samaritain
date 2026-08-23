<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $document->nom }} - {{ $metadata['titre'] ?? '' }}</title>
    @include('pdf.partials.styles', ['accentColor' => '#f47920', 'accentBgColor' => '#fff8f0'])
</head>

<body>
    <div class="container">

        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Attestation',
            'waveBase64' => $waveBase64,
            'logoBase64' => $logoBase64
        ])

        <!-- Bloc client -->
        @include('pdf.partials.client-info', ['client' => $client, 'artisan' => $artisan])

        <!-- Détails de l'attestation -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Référence</th>
                    <td>{{ $metadata['reference'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Titre</th>
                    <td>{{ $metadata['titre'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Date d'émission</th>
                    <td>{{ $metadata['date_emission'] ?? '—' }}</td>
                </tr>
            </table>
        </div>

        <!-- Description -->
        <div class="description-block">
            <h4>Contenu de l'attestation</h4>
            <p>{{ $metadata['description'] ?? 'Aucune description fournie.' }}</p>
        </div>

        <!-- Signature -->
        <div class="bottom-section">
            <div class="payment-info">
                <h4>Informations</h4>
                <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            </div>

            
        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer', ['message' => 'Merci de votre confiance.'])
    </div>
</body>

</html>