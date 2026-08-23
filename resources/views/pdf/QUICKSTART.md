# 🚀 Quick Start - Branding PDF Samaritain

Guide de démarrage rapide pour utiliser le système de branding des PDFs.

## 📋 Utiliser un template existant

Les templates suivants sont prêts à l'emploi :

```php
// Dans votre service
use App\Support\BrandingHelper;
use Barryvdh\DomPDF\Facade\Pdf;

public function generate(Document $document): string
{
    // 1. Récupérer les données de branding
    $brandingData = BrandingHelper::getEncodedImages();
    extract($brandingData); // $logoBase64, $waveBase64
    
    // 2. Préparer vos données
    $metadata = $document->metadata;
    $client = $document->client;
    $artisan = $client->artisan;
    
    // 3. Générer le HTML depuis le template
    $html = view('pdf.devis-template', compact(
        'document',
        'metadata',
        'client',
        'artisan',
        'logoBase64',
        'waveBase64'
        // + vos variables spécifiques
    ))->render();
    
    // 4. Générer le PDF
    $pdf = Pdf::loadHTML($html);
    $pdf->setPaper('A4', 'portrait');
    
    return $pdf->download('mon-document.pdf');
}
```

## 🆕 Créer un nouveau template

### Étape 1 : Créer le fichier Blade

Créez `resources/views/pdf/mon-template.blade.php` :

```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->nom }}</title>
    {{-- Inclure les styles partagés avec votre couleur --}}
    @include('pdf.partials.styles', [
        'accentColor' => '#f47920',      // Orange
        'accentBgColor' => '#fff8f0'     // Orange clair
    ])
</head>
<body>
    <div class="container">
        {{-- En-tête avec vague et logo --}}
        @include('pdf.partials.header', [
            'title' => 'Mon Document',
            'waveBase64' => $waveBase64,
            'logoBase64' => $logoBase64
        ])

        {{-- Informations client/artisan --}}
        @include('pdf.partials.client-info', [
            'client' => $client,
            'artisan' => $artisan
        ])

        {{-- Votre contenu spécifique --}}
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Colonne 1</th>
                        <th>Colonne 2</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Donnée 1</td>
                        <td>Donnée 2</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pied de page --}}
        @include('pdf.partials.footer', [
            'message' => 'Merci de votre confiance.'
        ])
    </div>
</body>
</html>
```

### Étape 2 : Créer le service (optionnel)

Si vous avez besoin d'un service dédié :

```php
<?php

namespace App\Services;

use App\Models\Document;
use App\Support\BrandingHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class MonDocumentPdfGenerator
{
    public function generate(Document $document): string
    {
        // Récupérer le branding
        $brandingData = BrandingHelper::getEncodedImages();
        extract($brandingData);
        
        // Préparer les données
        $metadata = $document->metadata;
        $client = $document->client;
        $artisan = $client->artisan;
        
        // Générer le HTML
        $html = view('pdf.mon-template', compact(
            'document',
            'metadata',
            'client',
            'artisan',
            'logoBase64',
            'waveBase64'
        ))->render();
        
        // Générer le PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        
        return $pdf->download($document->nom.'.pdf');
    }
}
```

### Étape 3 : Utiliser dans votre contrôleur

```php
use App\Services\MonDocumentPdfGenerator;

public function download(Document $document, MonDocumentPdfGenerator $generator)
{
    return $generator->generate($document);
}
```

## 🎨 Choisir une couleur

### Couleurs prédéfinies

```blade
{{-- Orange (par défaut) --}}
@include('pdf.partials.styles', [
    'accentColor' => '#f47920',
    'accentBgColor' => '#fff8f0'
])

{{-- Vert (documents signés) --}}
@include('pdf.partials.styles', [
    'accentColor' => '#10b981',
    'accentBgColor' => '#f0fdf4'
])

{{-- Rouge (alertes) --}}
@include('pdf.partials.styles', [
    'accentColor' => '#dc2626',
    'accentBgColor' => '#fef2f2'
])
```

### Ou utiliser la configuration

```php
// Dans votre service
$brandingData = BrandingHelper::getDataForDocument('devis');
// Inclut automatiquement accentColor et accentBgColor

$html = view('pdf.mon-template', $brandingData)->render();
```

## 🧩 Composants disponibles

### Header
```blade
@include('pdf.partials.header', [
    'title' => 'Mon titre',          // Requis
    'waveBase64' => $waveBase64,     // Requis
    'logoBase64' => $logoBase64      // Optionnel
])
```

### Client Info
```blade
@include('pdf.partials.client-info', [
    'client' => $client,    // Requis
    'artisan' => $artisan   // Requis
])
```

### Footer
```blade
@include('pdf.partials.footer', [
    'message' => 'Votre message'  // Optionnel
])
```

### Styles
```blade
@include('pdf.partials.styles', [
    'accentColor' => '#f47920',      // Optionnel (défaut: #f47920)
    'accentBgColor' => '#fff8f0'     // Optionnel (défaut: #fff8f0)
])
```

## 📊 Classes CSS utiles

### Tableaux
```html
<div class="table-wrapper">
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th class="text-right">Prix</th>
                <th class="text-center">Qté</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Item</td>
                <td class="text-right">100 FCFA</td>
                <td class="text-center">5</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Totaux
```html
<div class="totals-wrapper">
    <table class="totals-table">
        <tr>
            <td class="totals-label">Sous-total</td>
            <td class="totals-value">1000 FCFA</td>
        </tr>
    </table>
</div>

<div class="total-band">
    <span class="total-label">Total</span>
    <span class="total-value">1000 FCFA</span>
</div>
```

### Description
```html
<div class="description-block">
    <h4>Titre de la section</h4>
    <p>Votre texte de description...</p>
</div>
```

### Badge de statut
```html
<div style="text-align: center; padding: 15px 35px;">
    <span class="status-badge">✓ SIGNÉ</span>
</div>
```

## ⚠️ Limitations DomPDF

DomPDF ne supporte pas tout le CSS moderne :

❌ **Ne fonctionne PAS**
- `display: grid`
- `display: flex` (limité)
- Transformations CSS3 complexes
- Variables CSS (`--var`)
- `calc()`

✅ **Fonctionne**
- Tables (`<table>`)
- Positionnement absolu/relatif
- Bordures arrondies (`border-radius`)
- Couleurs (hex, rgb)
- Images en base64
- Padding, margin, width, height

## 💡 Conseils

1. **Utilisez les composants partagés** - Ne réinventez pas la roue
2. **Testez toujours** - Générez un PDF de test après modifications
3. **Images en base64** - Toujours encoder les images pour DomPDF
4. **Styles inline ou `<style>`** - Pas de CSS externe
5. **Tables pour la mise en page** - Plus fiable que flex/grid avec DomPDF
6. **Couleurs cohérentes** - Utilisez la configuration de branding

## 🧪 Tester rapidement

```bash
# Lancer les tests du helper
php artisan test --filter=BrandingHelperTest

# Vérifier la configuration
php artisan config:show branding

# Formater le code
vendor/bin/pint
```

## 📚 Documentation complète

- **Architecture** : `BRANDING.md`
- **Guide complet** : `resources/views/pdf/README.md`
- **Changelog** : `CHANGELOG_BRANDING.md`
- **Résumé** : `BRANDING_SUMMARY.md`

## ❓ Besoin d'aide ?

Consultez les templates existants comme exemples :
- `devis-template.blade.php` - Simple avec tableau
- `facture-template.blade.php` - Avec totaux et TVA
- `compte-rendu-template.blade.php` - Avec grille de photos
- `signed-devis.blade.php` - Avec section signature

---

**Happy coding! 🚀**
