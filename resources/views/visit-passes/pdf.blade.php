<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pass visite - {{ $visitPass->reference }}</title>
    @include('pdf.partials.styles', ['accentColor' => '#10b981', 'accentBgColor' => '#f0fdf4'])
    <style>
        /* Styles spécifiques au pass visite */
        .qr-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f0fdf4;
            border-radius: 8px;
        }
        .qr-container img {
            width: 150px;
            height: 150px;
            border: 3px solid #10b981;
            border-radius: 8px;
        }
        .qr-label {
            margin-top: 10px;
            font-size: 10px;
            color: #065f46;
            font-weight: bold;
        }
        .property-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #1a1a1a;
        }
        .visits-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Pass Visite',
            'waveBase64' => $waveBase64 ?? null,
            'logoBase64' => $logoBase64 ?? null
        ])

        <!-- Référence -->
        <div style="text-align: center; padding: 10px 35px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Référence</div>
            <div style="font-size: 16px; font-weight: bold; color: #10b981;">{{ $visitPass->reference }}</div>
        </div>

        <!-- QR Code -->
        <div class="qr-container">
            <img src="{{ $visitPass->getQrCodeBase64() }}" alt="QR Code">
            <div class="qr-label">Présentez ce QR Code pour accéder à la visite</div>
        </div>

        <!-- Bien -->
        <div class="description-block">
            <h4>Propriété</h4>
            <div class="property-title">{{ $visitPass->visitPassable->title }}</div>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Ville</th>
                    <td>{{ $visitPass->visitPassable->city->name ?? 'Brazzaville' }}</td>
                </tr>
                <tr>
                    <th>Adresse</th>
                    <td>{{ $visitPass->visitPassable->address ?? 'Non spécifiée' }}</td>
                </tr>
            </table>
        </div>

        <!-- Visiteur -->
        <div class="description-block">
            <h4>Informations du visiteur</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Nom complet</th>
                    <td>{{ $visitPass->holder_name }}</td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td>{{ $visitPass->phone }}</td>
                </tr>
                @if($visitPass->email)
                <tr>
                    <th>Email</th>
                    <td>{{ $visitPass->email }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Paiement -->
        <div class="description-block">
            <h4>Paiement</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Montant</th>
                    <td>{{ number_format($visitPass->amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <th>Statut</th>
                    <td><span class="status-badge">✓ PAYÉ</span></td>
                </tr>
                @if($visitPass->paid_at)
                <tr>
                    <th>Payé le</th>
                    <td>{{ $visitPass->paid_at->format('d/m/Y à H:i') }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Visites -->
        <div style="text-align: center; padding: 20px 35px;">
            <div class="visits-badge">
                {{ $visitPass->remaining_visits }} / {{ $visitPass->allowed_visits }} visites restantes
            </div>
        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer')
        <div style="text-align: center; padding: 5px 35px 15px; font-size: 8px; color: #999;">
            <p>Ce pass est valable pour {{ $visitPass->allowed_visits }} visites de la propriété indiquée.</p>
        </div>
    </div>
</body>
</html>