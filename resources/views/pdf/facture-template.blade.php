<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $document->nom }} - {{ $metadata['numero'] ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }

        /* ===== En-tête ===== */
        .header-wave {
            position: relative;
            width: 100%;
            height: 190px;
        }

        .header-wave-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 190px;
        }

        .header-content {
            position: relative;
            z-index: 2;
            padding: 30px 35px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .facture-number {
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
        }

        .logo-block {
            text-align: right;
        }

        .logo-block img {
            height: 40px;
        }

        .logo-block p {
            font-size: 9px;
            color: #333;
            margin-top: 2px;
        }

        /* ===== Bloc client ===== */
        .client-block {
            padding: 25px 35px 10px 35px;
        }

        .client-block h3 {
            font-size: 13px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .client-block p {
            font-size: 10px;
            color: #555;
            margin-bottom: 2px;
        }

        /* ===== Tableau ===== */
        .table-wrapper {
            padding: 20px 35px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1.5px solid #f47920;
            border-radius: 8px;
        }

        table th {
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            color: #1a1a1a;
            border-bottom: 1.5px solid #f47920;
        }

        table td {
            padding: 9px 12px;
            font-size: 10.5px;
            color: #333;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* ===== Totaux ===== */
        .totals-wrapper {
            padding: 5px 35px 0 35px;
        }

        .totals-table {
            width: 45%;
            margin-left: auto;
            border: none;
        }

        .totals-table td {
            padding: 4px 0;
            font-size: 11px;
            border: none;
        }

        .totals-table .totals-label {
            color: #666;
        }

        .totals-table .totals-value {
            text-align: right;
            font-weight: bold;
        }

        .total-band {
            margin: 15px 35px 0 35px;
            background: #f47920;
            color: white;
            padding: 12px 20px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-band .total-label {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .total-band .total-value {
            font-size: 16px;
            font-weight: bold;
        }

        /* ===== Bas de page ===== */
        .bottom-section {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 30px 35px;
        }

        .payment-info {
            flex: 1;
        }

        .payment-info h4 {
            font-size: 11px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 6px;
        }

        .payment-info p {
            font-size: 9.5px;
            color: #555;
            margin-bottom: 2px;
        }

        .payment-info .terms {
            margin-top: 14px;
        }

        .signature-box {
            flex: 1;
            border: 1.5px solid #f47920;
            border-radius: 8px;
            padding: 15px;
            min-height: 100px;
        }

        .signature-box .sig-label {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            margin-bottom: 25px;
        }

        /* ===== Pied de page ===== */
        .footer {
            text-align: center;
            padding: 15px 35px 25px 35px;
            font-size: 8.5px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- En-tête -->
        <div class="header-wave">
            <img src="{{ $waveBase64 }}" class="header-wave-img" alt="">
            <div class="header-content">
                <div class="facture-number">
                    Facture n° {{ $metadata['numero'] ?? '—' }}
                </div>
                {{-- <div class="logo-block">
                    <img src="{{ $logoBase64 }}" alt="Samaritain">
                    <p>VIVEZ SERENEMENT</p>
                </div> --}}
            </div>
        </div>

        <!-- Bloc client -->
        <div class="client-block">
            <h3>Artisan : {{ $artisan->business_name ?? 'N/A' }}</h3>
            <p>{{ $artisan->profession ?? '' }} — {{ $artisan->city ?? '' }}</p>
            <p>{{ $artisan->phone ?? '' }}</p>

            <h3 style="margin-top: 15px;">Client : {{ $client->nom ?? 'N/A' }}</h3>
            <p>{{ $client->email ?? '' }}</p>
            <p>{{ $client->telephone ?? '' }}</p>
            <p>Type : {{ $client->type?->label() ?? 'N/A' }}</p>
        </div>

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
        <div class="footer">
            <p>Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
            <p>Merci de votre confiance.</p>
        </div>
    </div>
</body>

</html>