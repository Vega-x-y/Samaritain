<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrat de Bail</title>
    @include('pdf.partials.styles', ['accentColor' => '#0d9488', 'accentBgColor' => '#f0fdfa'])
    <style>
        /* Styles spécifiques au contrat */
        .clause {
            margin-bottom: 12px;
            padding: 8px 35px 8px 45px;
            background: #f0fdfa;
            border-left: 3px solid #0d9488;
        }
        .clause p {
            margin: 3px 0;
        }
        .highlight {
            background: #f0fdf4;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #bbf7d0;
            margin: 10px 35px;
        }
        .highlight strong {
            color: #059669;
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
            border-top: 1px solid #374151;
            margin-top: 50px;
            padding-top: 8px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Contrat de Location — Bail d\'Habitation',
            'waveBase64' => $waveBase64 ?? null,
            'logoBase64' => $logoBase64 ?? null
        ])

        <p style="text-align: center; color: #6b7280; font-size: 10px; padding: 0 35px;">
            Fait le {{ now()->format('d/m/Y') }} à Brazzaville
        </p>

        <!-- Parties au Contrat -->
        <div class="description-block">
            <h4>1. Parties au Contrat</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Bailleur (Propriétaire)</th>
                    <td><strong>{{ auth()->user()->name }}</strong></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ auth()->user()->email }}</td>
                </tr>
                <tr>
                    <th>Téléphone</th>
                    <td>{{ auth()->user()->phone ?? 'Non spécifié' }}</td>
                </tr>
            </table>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Locataire</th>
                    <td><strong>{{ $contract->tenant_name }}</strong></td>
                </tr>
                @if($contract->tenant_email)
                <tr>
                    <th>Email</th>
                    <td>{{ $contract->tenant_email }}</td>
                </tr>
                @endif
                @if($contract->tenant_phone)
                <tr>
                    <th>Téléphone</th>
                    <td>{{ $contract->tenant_phone }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Désignation du Logement -->
        <div class="description-block">
            <h4>2. Désignation du Logement</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Adresse</th>
                    <td><strong>{{ $property->title }}</strong></td>
                </tr>
                <tr>
                    <th>Situation</th>
                    <td>{{ $property->address }}, {{ $property->city->name ?? 'Non spécifiée' }}</td>
                </tr>
                @if($property->surface)
                <tr>
                    <th>Surface</th>
                    <td>{{ $property->surface }} m²</td>
                </tr>
                @endif
                @if($property->bedrooms)
                <tr>
                    <th>Pièces</th>
                    <td>{{ $property->bedrooms }} chambre(s)</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Durée et Conditions Financières -->
        <div class="description-block">
            <h4>3. Durée du Bail</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Date de début</th>
                    <td>{{ $contract->start_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Date de fin</th>
                    <td>{{ $contract->end_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Durée</th>
                    <td>{{ $contract->start_date->diffInMonths($contract->end_date) }} mois</td>
                </tr>
            </table>
        </div>

        <div class="description-block">
            <h4>4. Conditions Financières</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Loyer mensuel</th>
                    <td><strong>{{ number_format($contract->monthly_rent, 0, ',', ' ') }} FCFA</strong></td>
                </tr>
                @if($contract->security_deposit > 0)
                <tr>
                    <th>Caution</th>
                    <td>{{ number_format($contract->security_deposit, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                @if($contract->additional_charges > 0)
                <tr>
                    <th>Charges</th>
                    <td>{{ number_format($contract->additional_charges, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                <tr>
                    <th>Paiement dû le</th>
                    <td>{{ $contract->payment_day }} de chaque mois</td>
                </tr>
            </table>
        </div>

        <!-- Clauses -->
        <div class="description-block">
            <h4>5. Clauses Particulières</h4>
        </div>

        <div class="clause">
            <p><strong>Article 1 - Objet du contrat</strong></p>
            <p>Le bailleur loue au locataire le logement désigné ci-dessus pour usage d'habitation principale.</p>
        </div>

        <div class="clause">
            <p><strong>Article 2 - Paiement du loyer</strong></p>
            <p>Le locataire s'engage à payer le loyer mensuel de {{ number_format($contract->monthly_rent, 0, ',', ' ') }} FCFA le {{ $contract->payment_day }} de chaque mois.</p>
        </div>

        <div class="clause">
            <p><strong>Article 3 - Charges</strong></p>
            <p>Les charges comprennent l'eau, l'électricité et l'entretien des parties communes. Montant mensuel : {{ number_format($contract->additional_charges ?? 0, 0, ',', ' ') }} FCFA.</p>
        </div>

        <div class="clause">
            <p><strong>Article 4 - Durée et renouvellement</strong></p>
            <p>Le présent bail est conclu pour une durée de {{ $contract->start_date->diffInMonths($contract->end_date) }} mois, du {{ $contract->start_date->format('d/m/Y') }} au {{ $contract->end_date->format('d/m/Y') }}.</p>
        </div>

        <!-- Signatures -->
        @if($contract->signatures->isNotEmpty())
        <div class="highlight">
            <strong>✓ Ce contrat a été signé électroniquement</strong> par les parties le {{ $contract->signatures->first()->created_at->format('d/m/Y à H:i') }}
        </div>
        @endif

        <div class="signatures">
            <div class="signature-box">
                <div>Le Bailleur</div>
                <div class="line">{{ auth()->user()->name }}</div>
            </div>
            <div class="signature-box">
                <div>Le Locataire</div>
                <div class="line">{{ $contract->tenant_name }}</div>
            </div>
        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer', ['message' => 'Contrat de bail généré via Samaritain Immobilier'])
    </div>
</body>
</html>
