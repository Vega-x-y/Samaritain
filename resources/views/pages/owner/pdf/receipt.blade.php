<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Quittance de Loyer</title>
    @include('pdf.partials.styles', ['accentColor' => '#0d9488', 'accentBgColor' => '#f0fdfa'])
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Quittance de Loyer',
            'waveBase64' => $waveBase64 ?? null,
            'logoBase64' => $logoBase64 ?? null
        ])

        <!-- Numéro de quittance -->
        <div style="text-align: center; padding: 10px 35px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Quittance N°</div>
            <div style="font-size: 16px; font-weight: bold; color: #0d9488;">{{ str_pad($rentPayment->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <!-- Bailleur et Locataire -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Bailleur / Propriétaire</th>
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

        <!-- Propriété -->
        <div class="description-block">
            <h4>Propriété concernée</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Adresse</th>
                    <td>{{ $property->title }}</td>
                </tr>
                <tr>
                    <th>Situation</th>
                    <td>{{ $property->address }}, {{ $property->city->name ?? 'Non spécifiée' }}</td>
                </tr>
            </table>
        </div>

        <!-- Montants -->
        <div class="description-block">
            <h4>Détails du paiement</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 50%;">Période</th>
                    <th class="text-right">Montant</th>
                </tr>
                <tr>
                    <td>Loyer du mois de {{ $rentPayment->payment_date->format('F Y') }}</td>
                    <td class="text-right">{{ number_format($contract->monthly_rent, 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($contract->additional_charges > 0)
                <tr>
                    <td>Charges</td>
                    <td class="text-right">{{ number_format($contract->additional_charges, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Total -->
        <div class="total-band">
            <span class="total-label">TOTAL PAYÉ</span>
            <span class="total-value">{{ number_format($rentPayment->amount_paid, 0, ',', ' ') }} FCFA</span>
        </div>

        <!-- Informations de paiement -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Date de paiement</th>
                    <td>{{ $rentPayment->paid_at ? $rentPayment->paid_at->format('d/m/Y à H:i') : 'Non payé' }}</td>
                </tr>
                <tr>
                    <th>Méthode de paiement</th>
                    <td>{{ ucfirst($rentPayment->payment_method ?? 'Non spécifiée') }}</td>
                </tr>
                @if($rentPayment->transaction_reference)
                <tr>
                    <th>Référence</th>
                    <td>{{ $rentPayment->transaction_reference }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Signature -->
        <div style="padding: 30px 35px;">
            <div style="border: 1.5px solid #0d9488; border-radius: 8px; padding: 15px; min-height: 100px;">
                <div style="font-size: 10px; font-weight: bold; color: #333; margin-bottom: 25px;">
                    Signature du bailleur
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        @include('pdf.partials.footer', ['message' => 'Pour toute réclamation, veuillez nous contacter dans les 48h.'])
    </div>
</body>
</html>
