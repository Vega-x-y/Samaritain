<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>État des lieux</title>
    @include('pdf.partials.styles', ['accentColor' => '#1a56db', 'accentBgColor' => '#eff6ff'])
    <style>
        /* Styles spécifiques à l'état des lieux */
        .status-clean { color: #059669; font-weight: bold; }
        .status-good { color: #2563eb; font-weight: bold; }
        .status-fair { color: #d97706; font-weight: bold; }
        .status-damaged { color: #dc2626; font-weight: bold; }
        .notes {
            background: #f3f4f6;
            padding: 12px 35px;
            border-radius: 5px;
            margin: 10px 35px;
            font-size: 11px;
        }
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 35px;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-box .line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 8px;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'État des Lieux',
            'waveBase64' => $waveBase64 ?? null,
            'logoBase64' => $logoBase64 ?? null
        ])

        <!-- Type d'état des lieux -->
        <div style="text-align: center; padding: 10px 35px;">
            @php
                $typeLabel = $inspection->type === 'entry' ? 'État des lieux d\'ENTRÉE' : 'État des lieux de SORTIE';
                $typeColor = $inspection->type === 'entry' ? '#1a56db' : '#d97706';
            @endphp
            <span style="display: inline-block; padding: 8px 20px; background: {{ $typeColor }}; color: white; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
                {{ $typeLabel }}
            </span>
        </div>

        <!-- Informations générales -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Propriété</th>
                    <td>{{ $inspection->property->title }}</td>
                </tr>
                <tr>
                    <th>Adresse</th>
                    <td>{{ $inspection->property->address }}</td>
                </tr>
                <tr>
                    <th>Locataire</th>
                    <td>{{ $inspection->contract->tenant_name ?? 'Non spécifié' }}</td>
                </tr>
                <tr>
                    <th>Date de l'inspection</th>
                    <td>{{ $inspection->inspection_date->format('d/m/Y') }}</td>
                </tr>
                @if($inspection->conducted_by)
                <tr>
                    <th>Réalisé par</th>
                    <td>{{ $inspection->conducted_by }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Détail des pièces -->
        <div class="description-block">
            <h4>Détail de l'inspection par pièce</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Pièce</th>
                        <th style="width: 15%;">État</th>
                        <th>Observations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inspection->rooms ?? [] as $room)
                    <tr>
                        <td>{{ $room['name'] ?? 'Pièce' }}</td>
                        <td>
                            <span class="status-{{ strtolower($room['condition'] ?? 'good') }}">
                                {{ ucfirst($room['condition'] ?? 'good') }}
                            </span>
                        </td>
                        <td>{{ $room['notes'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Notes générales -->
        @if($inspection->notes)
        <div class="description-block">
            <h4>Notes générales</h4>
        </div>
        <div class="notes">
            {{ $inspection->notes }}
        </div>
        @endif

        <!-- Compteurs -->
        @if($inspection->meters && count($inspection->meters) > 0)
        <div class="description-block">
            <h4>Relevés des compteurs</h4>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="text-right">Relevé</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inspection->meters as $meter)
                    <tr>
                        <td>{{ ucfirst($meter['type'] ?? 'Compteur') }}</td>
                        <td class="text-right">{{ $meter['reading'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div>Le Propriétaire</div>
                <div class="line">{{ auth()->user()->name }}</div>
            </div>
            <div class="signature-box">
                <div>Le Locataire</div>
                <div class="line">{{ $inspection->contract->tenant_name ?? '..................' }}</div>
            </div>
        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer', ['message' => 'État des lieux réalisé via Samaritain Immobilier'])
    </div>
</body>
</html>
