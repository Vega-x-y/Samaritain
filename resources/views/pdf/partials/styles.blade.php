{{-- 
    Styles partagés pour tous les PDFs Samaritain
    Usage: @include('pdf.partials.styles', ['accentColor' => '#f47920'])
--}}

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

    /* ===== En-tête avec wave ===== */
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

    .document-title {
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
        color: white;
        margin-top: 2px;
        font-weight: 500;
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

    /* ===== Tableaux ===== */
    .table-wrapper {
        padding: 20px 35px;
    }

    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1.5px solid {{ $accentColor ?? '#f47920' }};
        border-radius: 8px;
    }

    table th {
        padding: 10px 12px;
        text-align: left;
        font-size: 10px;
        font-weight: bold;
        color: #1a1a1a;
        border-bottom: 1.5px solid {{ $accentColor ?? '#f47920' }};
        background: {{ $accentBgColor ?? '#fff8f0' }};
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
        background: {{ $accentColor ?? '#f47920' }};
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

    /* ===== Description ===== */
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

    .payment-info .terms {
        margin-top: 14px;
    }

    .signature-box {
        flex: 1;
        border: 1.5px solid {{ $accentColor ?? '#f47920' }};
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

    /* ===== Badge statut ===== */
    .status-badge {
        display: inline-block;
        background: #10b981;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        margin-top: 10px;
    }

    /* ===== Pied de page ===== */
    .footer {
        text-align: center;
        padding: 15px 35px 25px 35px;
        font-size: 8.5px;
        color: #999;
    }

    .footer p {
        margin: 2px 0;
    }

    /* ===== Photos (compte-rendu) ===== */
    .photos-wrapper {
        padding: 0 35px 20px 35px;
    }

    .photos-wrapper h4 {
        font-size: 11px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 10px;
    }

    .photos-grid {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .photo-item {
        flex: 1;
        min-width: 200px;
    }

    .photo-item img {
        width: 100%;
        border: 1.5px solid {{ $accentColor ?? '#f47920' }};
        border-radius: 8px;
    }

    .photo-item p {
        font-size: 9px;
        color: #666;
        text-align: center;
        margin-top: 4px;
    }
</style>
