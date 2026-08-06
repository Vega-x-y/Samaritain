<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $document->nom }} - {{ $metadata['titre'] ?? '' }}</title>
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

        .att-title {
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

        /* ===== Détails de l'attestation ===== */
        .details-wrapper {
            padding: 20px 35px;
        }

        .details-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1.5px solid #dc2626;
            border-radius: 8px;
        }

        .details-table th {
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            color: #1a1a1a;
            border-bottom: 1.5px solid #dc2626;
            background: #fef2f2;
        }

        .details-table td {
            padding: 9px 12px;
            font-size: 10.5px;
            color: #333;
        }

        .description-block {
            padding: 20px 35px;
        }

        .description-block h4 {
            font-size: 11px;
            font-weight: bold;
            color: #1a1a1a;
            margin-bottom: 6px;
        }

        .description-block p {
            font-size: 10.5px;
            color: #555;
            line-height: 1.6;
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

        .signature-box {
            flex: 1;
            border: 1.5px solid #dc2626;
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
                <div class="att-title">
                    Attestation
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

        <!-- Détails de l'attestation -->
        <div class="details-wrapper">
            <table class="details-table">
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
        <div class="footer">
            <p>Attestation — {{ $document->nom }}</p>
            <p>Merci de votre confiance.</p>
        </div>
    </div>
</body>

</html>