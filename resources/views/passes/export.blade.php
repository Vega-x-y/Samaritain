<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Pass - {{ $pass->holder_name }}</title>
    @include('pdf.partials.styles', ['accentColor' => '#10b981', 'accentBgColor' => '#f0fdf4'])
    <style>
        /* Styles spécifiques au pass */
        .qr-section {
            text-align: center;
            margin: 30px 35px;
            padding: 20px;
            background: #f0fdf4;
            border-radius: 8px;
        }

        .qr-section img {
            width: 160px;
            height: 160px;
            border: 3px solid #10b981;
            border-radius: 8px;
        }

        .qr-label {
            margin-top: 10px;
            font-size: 10px;
            color: #065f46;
            font-weight: bold;
        }

        .info-value.mono {
            font-family: monospace;
            font-size: 11px;
        }

        .progress-wrapper {
            padding: 20px 35px;
        }

        .visits {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 8px;
        }

        .visits .label {
            font-size: 11px;
            color: #666;
        }

        .visits .value {
            font-size: 13px;
            font-weight: 600;
        }

        .progress-bar {
            background: #e5e7eb;
            height: 4px;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            background: #10b981;
            height: 100%;
            width: {{ ($pass->remaining_visits / $pass->allowed_visits) * 100 }}%;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Pass de Visite',
            'waveBase64' => $waveBase64 ?? null,
            'logoBase64' => $logoBase64 ?? null
        ])

        <!-- Statut -->
        <div style="text-align: center; padding: 10px 35px;">
            @php
                $statusColor = match($pass->status) {
                    'actif' => '#10b981',
                    default => '#9ca3af'
                };
            @endphp
            <span style="display: inline-block; padding: 5px 15px; background: {{ $statusColor }}; color: white; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase;">
                {{ ucfirst($pass->status) }}
            </span>
        </div>

        <!-- QR Code -->
        <div class="qr-section">
            <img src="{{ $pass->getQrCodeBase64() }}" alt="QR Code">
            <div class="qr-label">Présentez ce QR Code à l'entrée</div>
        </div>

        <!-- Informations -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Titulaire</th>
                    <td>{{ $pass->holder_name }}</td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td>{{ $pass->phone }}</td>
                </tr>
                @if ($pass->email)
                <tr>
                    <th>Email</th>
                    <td>{{ $pass->email }}</td>
                </tr>
                @endif
                <tr>
                    <th>Date d'émission</th>
                    <td>{{ $pass->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Période de validité</th>
                    <td>Du {{ $pass->start_date->format('d/m/Y') }} au {{ $pass->expiration_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>UUID</th>
                    <td><span class="info-value mono">{{ $pass->uuid }}</span></td>
                </tr>
            </table>
        </div>

        <!-- Visites restantes -->
        <div class="progress-wrapper">
            <div class="visits">
                <span class="label">Visites restantes</span>
                <span class="value">{{ $pass->remaining_visits }} / {{ $pass->allowed_visits }}</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer')
        <div style="text-align: center; padding: 5px 35px 15px; font-size: 8px; color: #999;">
            <p>Ce document est généré automatiquement. Toute falsification est interdite.</p>
            <p>Réf. {{ substr($pass->uuid, 0, 13) }}…</p>
        </div>
    </div>
</body>

</html>