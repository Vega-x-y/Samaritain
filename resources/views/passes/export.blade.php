<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Pass - {{ $pass->holder_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            padding: 40px;
            background: #fff;
            color: #1f2937;
        }

        /* Container principal */
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        /* En-tête */
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Badge officiel */
        .official-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 11px;
            margin-top: 12px;
        }

        /* QR Code */
        .qr-section {
            text-align: center;
            padding: 30px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .qr-code {
            display: inline-block;
            padding: 16px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .qr-code img {
            display: block;
            margin: 0 auto;
            width: 200px;
            height: 200px;
        }

        .qr-label {
            margin-top: 12px;
            font-size: 12px;
            color: #6b7280;
            letter-spacing: 1px;
        }

        /* Informations */
        .info-section {
            padding: 30px;
        }

        .info-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .info-item {
            margin-bottom: 0;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
            display: block;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
            word-break: break-word;
        }

        .info-value.uuid {
            font-family: monospace;
            font-size: 13px;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
        }

        /* Statut */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-actif {
            background: #d1fae5;
            color: #065f46;
        }

        .status-actif::before {
            content: "●";
            color: #10b981;
            font-size: 10px;
        }

        .status-expiré {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-expiré::before {
            content: "●";
            color: #ef4444;
            font-size: 10px;
        }

        .status-utilisé {
            background: #fed7aa;
            color: #92400e;
        }

        .status-utilisé::before {
            content: "✓";
            color: #f59e0b;
            font-size: 10px;
        }

        .status-suspendu {
            background: #e5e7eb;
            color: #374151;
        }

        .status-suspendu::before {
            content: "●";
            color: #6b7280;
            font-size: 10px;
        }

        /* Barre de progression */
        .progress-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .progress-bar {
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            height: 8px;
        }

        .progress-fill {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            height: 100%;
            border-radius: 10px;
            width: {{ ($pass->remaining_visits / $pass->allowed_visits) * 100 }}%;
        }

        /* Pied de page */
        .footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 11px;
            color: #6b7280;
        }

        .footer p {
            margin: 4px 0;
        }

        /* Responsive */
        @media print {
            body {
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
            }
            
            .header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .progress-fill {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Full width sur mobile */
        @media (max-width: 640px) {
            .info-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1>🎫 Pass de Visite</h1>
            <p>Document officiel d'accès</p>
            <div class="official-badge">
                {{ $pass->status === 'actif' ? '✓ VALIDE' : '⚠️ NON VALIDE' }}
            </div>
        </div>

        <!-- QR Code -->
        <div class="qr-section">
            <div class="qr-code">
                <img src="{{ $pass->getQrCodeBase64() }}" width="200" height="200" alt="QR Code">
            </div>
            <div class="qr-label">
                Présentez ce QR Code à l'entrée
            </div>
        </div>

        <!-- Informations -->
        <div class="info-section">
            <div class="info-title">
                Informations du titulaire
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Titulaire</span>
                    <span class="info-value">{{ $pass->holder_name }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Téléphone</span>
                    <span class="info-value">{{ $pass->phone }}</span>
                </div>
                
                @if ($pass->email)
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">{{ $pass->email }}</span>
                </div>
                @endif
                
                <div class="info-item">
                    <span class="info-label">UUID</span>
                    <span class="info-value uuid">{{ $pass->uuid }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Date d'émission</span>
                    <span class="info-value">{{ $pass->created_at->format('d/m/Y') }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Statut</span>
                    <span class="status status-{{ $pass->status }}">{{ ucfirst($pass->status) }}</span>
                </div>
            </div>
            
            <!-- Période de validité -->
            <div style="margin-top: 24px;">
                <div class="info-item">
                    <span class="info-label">Période de validité</span>
                    <span class="info-value">
                        Du {{ $pass->start_date->format('d/m/Y') }} au {{ $pass->expiration_date->format('d/m/Y') }}
                    </span>
                </div>
            </div>
            
            <!-- Progression des visites -->
            <div class="progress-section">
                <div class="progress-label">
                    <span>Visites utilisées</span>
                    <span><strong>{{ $pass->allowed_visits - $pass->remaining_visits }}</strong> / {{ $pass->allowed_visits }}</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-label" style="margin-top: 8px;">
                    <span>Visites restantes</span>
                    <span><strong>{{ $pass->remaining_visits }}</strong></span>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <div class="footer">
            <p>🔒 Ce document est généré automatiquement. Toute falsification est interdite.</p>
            <p>📅 Document émis le {{ now()->format('d/m/Y à H:i:s') }}</p>
            <p>🏷️ Référence: {{ substr($pass->uuid, 0, 13) }}...</p>
        </div>
    </div>
</body>

</html>