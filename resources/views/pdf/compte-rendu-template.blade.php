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
            'title' => "Compte rendu d'intervention",
            'waveBase64' => $waveBase64,
            'logoBase64' => $logoBase64
        ])

        <!-- Bloc client -->
        @include('pdf.partials.client-info', ['client' => $client, 'artisan' => $artisan])

        <!-- Détails du compte rendu -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Titre</th>
                    <td>{{ $metadata['titre'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Date d'intervention</th>
                    <td>{{ $metadata['date_intervention'] ?? '—' }}</td>
                </tr>
                <tr>
                    <th>Durée</th>
                    <td>{{ $metadata['duree'] ?? '—' }} heure(s)</td>
                </tr>
            </table>
        </div>

        <!-- Description -->
        <div class="description-block">
            <h4>Description des travaux</h4>
            <p>{{ $metadata['description'] ?? 'Aucune description fournie.' }}</p>
        </div>

        <!-- Photos avant/après -->
        @if(count($photosAvantBase64) > 0 || count($photosApresBase64) > 0)
            <div class="photos-wrapper">
                <h4>Photos de l'intervention</h4>
                <div class="photos-grid">
                    @foreach($photosAvantBase64 as $photo)
                        <div class="photo-item">
                            <img src="{{ $photo }}" alt="Avant">
                            <p>Avant</p>
                        </div>
                    @endforeach
                    @foreach($photosApresBase64 as $photo)
                        <div class="photo-item">
                            <img src="{{ $photo }}" alt="Après">
                            <p>Après</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

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