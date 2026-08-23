<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture</title>
    @include('pdf.partials.styles', ['accentColor' => '#0d9488', 'accentBgColor' => '#f0fdfa'])
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Facture',
            'waveBase64' => $waveBase64 ?? null,
            'logoBase64' => $logoBase64 ?? null
        ])

        <!-- Numéro de facture -->
        <div style="text-align: center; padding: 10px 35px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 5px;">Facture N°</div>
            <div style="font-size: 16px; font-weight: bold; color: #0d9488;">{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <!-- Émetteur et Destinataire -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Émetteur</th>
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
                    <th style="width: 30%;">Destinataire</th>
                    <td><strong>{{ $invoice->tenant_name ?? 'Non spécifié' }}</strong></td>
                </tr>
                @if($invoice->tenant_email)
                <tr>
                    <th>Email</th>
                    <td>{{ $invoice->tenant_email }}</td>
                </tr>
                @endif
                @if($invoice->tenant_phone)
                <tr>
                    <th>Téléphone</th>
                    <td>{{ $invoice->tenant_phone }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Informations de la facture -->
        <div class="description-block">
            <h4>Détails de la facture</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Date d'émission</th>
                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                </tr>
                @if($invoice->due_date)
                <tr>
                    <th>Date d'échéance</th>
                    <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                </tr>
                @endif
                <tr>
                    <th>Propriété</th>
                    <td>{{ $property->title ?? 'Non spécifiée' }}</td>
                </tr>
                @if($invoice->description)
                <tr>
                    <th>Description</th>
                    <td>{{ $invoice->description }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Montants -->
        <div class="description-block">
            <h4>Détails des montants</h4>
        </div>

        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 50%;">Désignation</th>
                    <th class="text-right">Montant</th>
                </tr>
                @foreach(json_decode($invoice->items ?? '[]', true) as $item)
                <tr>
                    <td>{{ $item['description'] ?? 'Service' }}</td>
                    <td class="text-right">{{ number_format($item['amount'] ?? 0, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
                @if(empty(json_decode($invoice->items ?? '[]', true)))
                <tr>
                    <td>{{ $invoice->description ?? 'Service' }}</td>
                    <td class="text-right">{{ number_format($invoice->amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Total -->
        <div class="total-band">
            <span class="total-label">MONTANT TOTAL</span>
            <span class="total-value">{{ number_format($invoice->amount, 0, ',', ' ') }} FCFA</span>
        </div>

        <!-- Statut -->
        <div style="text-align: center; padding: 20px 35px;">
            @php
                $statusColor = $invoice->status === 'paid' ? '#10b981' : '#dc2626';
                $statusLabel = $invoice->status === 'paid' ? '✓ PAYÉE' : '⚠ IMPAYÉE';
            @endphp
            <span style="display: inline-block; padding: 8px 20px; background: {{ $statusColor }}; color: white; border-radius: 20px; font-size: 12px; font-weight: bold;">
                {{ $statusLabel }}
            </span>
        </div>

        @if($invoice->status === 'paid' && $invoice->paid_at)
        <div class="table-wrapper">
            <table>
                <tr>
                    <th style="width: 30%;">Date de paiement</th>
                    <td>{{ $invoice->paid_at->format('d/m/Y à H:i') }}</td>
                </tr>
                @if($invoice->payment_method)
                <tr>
                    <th>Méthode de paiement</th>
                    <td>{{ ucfirst($invoice->payment_method) }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        <!-- Pied de page -->
        @include('pdf.partials.footer', ['message' => 'Merci pour votre confiance.'])
    </div>
</body>
</html>
