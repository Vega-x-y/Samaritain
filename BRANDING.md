# 🎨 Branding PDF Samaritain

Ce document décrit l'implémentation du branding cohérent pour tous les PDFs générés par l'application Samaritain.

## 📋 Résumé des changements

Tous les PDFs générés par l'application suivent maintenant un branding cohérent et uniforme :

### ✅ Ce qui a été fait

1. **Création de composants réutilisables** (`resources/views/pdf/partials/`)
   - `styles.blade.php` - Styles CSS communs
   - `header.blade.php` - En-tête avec vague et logo
   - `client-info.blade.php` - Bloc informations client/artisan
   - `footer.blade.php` - Pied de page standard

2. **Mise à jour de tous les templates PDF**
   - ✅ `devis-template.blade.php`
   - ✅ `facture-template.blade.php`
   - ✅ `attestation-template.blade.php`
   - ✅ `compte-rendu-template.blade.php`
   - ✅ `signed-devis.blade.php`

3. **Création d'un système de configuration** (`config/branding.php`)
   - Couleurs centralisées (primary, success, danger)
   - Chemins des images
   - Slogan ("VIVEZ SEREINEMENT")
   - Mapping couleurs par type de document

4. **Classe Helper** (`app/Support/BrandingHelper.php`)
   - `getEncodedImages()` - Récupère logo et vague en base64
   - `getColorForDocument()` - Couleur principale par type
   - `getBackgroundForDocument()` - Couleur de fond par type
   - `getDataForDocument()` - Toutes les données de branding

5. **Mise à jour des services**
   - ✅ `DevisPdfGenerator.php` - Utilise BrandingHelper
   - ✅ `DocumentPdfGenerator.php` - Utilise BrandingHelper
   - ✅ `PdfSignatureService.php` - Utilise BrandingHelper

6. **Tests unitaires** (`tests/Unit/BrandingHelperTest.php`)
   - 4 tests qui valident le fonctionnement du BrandingHelper
   - Tous les tests passent ✅

7. **Documentation**
   - `resources/views/pdf/README.md` - Guide complet d'utilisation
   - `BRANDING.md` (ce fichier) - Vue d'ensemble des changements

## 🎨 Charte graphique

### Couleurs principales

| Couleur | Hex | Usage |
|---------|-----|-------|
| Orange | `#f47920` | Devis, factures, comptes-rendus, attestations |
| Vert | `#10b981` | Documents signés, succès |
| Rouge | `#dc2626` | Alertes (optionnel) |

### Couleurs de fond

| Couleur | Hex | Usage |
|---------|-----|-------|
| Orange clair | `#fff8f0` | Arrière-plan tableaux (documents standards) |
| Vert clair | `#f0fdf4` | Arrière-plan tableaux (documents signés) |
| Rouge clair | `#fef2f2` | Arrière-plan tableaux (alertes) |

### Images

- **Logo** : `public/images/logo-samaritain.png`
- **Vague** : `public/images/header-wave.png`

### Typographie

- **Police** : Arial, sans-serif
- **Taille de base** : 11px
- **Titre document** : 20px, gras, blanc
- **Titres sections** : 13px, gras, #1a1a1a

## 📁 Structure des fichiers

```
resources/views/pdf/
├── partials/
│   ├── styles.blade.php       # Styles communs
│   ├── header.blade.php       # En-tête avec vague
│   ├── client-info.blade.php  # Infos client/artisan
│   └── footer.blade.php       # Pied de page
├── attestation-template.blade.php
├── compte-rendu-template.blade.php
├── devis-template.blade.php
├── facture-template.blade.php
├── signed-devis.blade.php
└── README.md                  # Documentation complète

config/
└── branding.php              # Configuration branding

app/Support/
└── BrandingHelper.php        # Helper de branding

app/Services/
├── DevisPdfGenerator.php     # Utilise BrandingHelper
├── DocumentPdfGenerator.php  # Utilise BrandingHelper
└── PdfSignatureService.php   # Utilise BrandingHelper
```

## 🔧 Utilisation

### Créer un nouveau template PDF

```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Document</title>
    @include('pdf.partials.styles', [
        'accentColor' => '#f47920',
        'accentBgColor' => '#fff8f0'
    ])
</head>
<body>
    <div class="container">
        @include('pdf.partials.header', [
            'title' => 'Mon Document',
            'waveBase64' => $waveBase64,
            'logoBase64' => $logoBase64
        ])

        @include('pdf.partials.client-info', [
            'client' => $client,
            'artisan' => $artisan
        ])

        <!-- Votre contenu spécifique -->

        @include('pdf.partials.footer', [
            'message' => 'Merci de votre confiance.'
        ])
    </div>
</body>
</html>
```

### Utiliser BrandingHelper dans un service

```php
use App\Support\BrandingHelper;

// Option 1 : Récupérer seulement les images
$brandingData = BrandingHelper::getEncodedImages();
// ['logoBase64' => '...', 'waveBase64' => '...']

// Option 2 : Récupérer toutes les données pour un type de document
$brandingData = BrandingHelper::getDataForDocument('devis');
// [
//     'logoBase64' => '...',
//     'waveBase64' => '...',
//     'accentColor' => '#f47920',
//     'accentBgColor' => '#fff8f0',
//     'slogan' => 'VIVEZ SEREINEMENT'
// ]

// Option 3 : Récupérer juste la couleur
$color = BrandingHelper::getColorForDocument('signed');
// '#10b981'
```

## 🔄 Modifier le branding

### Changer les couleurs

Éditez `config/branding.php` :

```php
'colors' => [
    'primary' => '#f47920',  // Nouvelle couleur orange
    'success' => '#10b981',  // Nouvelle couleur verte
    'danger' => '#dc2626',   // Nouvelle couleur rouge
],
```

### Changer le logo ou la vague

1. Remplacez les fichiers dans `public/images/`
2. Ou modifiez les chemins dans `config/branding.php`

```php
'images' => [
    'logo' => 'images/mon-nouveau-logo.png',
    'wave' => 'images/ma-nouvelle-vague.png',
],
```

### Changer le slogan

```php
'slogan' => 'MON NOUVEAU SLOGAN',
```

## ✅ Tests

Exécutez les tests pour vérifier le branding :

```bash
php artisan test --filter=BrandingHelperTest
```

Tous les tests doivent passer :
- ✅ `getEncodedImages retourne les images encodées`
- ✅ `getColorForDocument retourne la bonne couleur`
- ✅ `getBackgroundForDocument retourne le bon fond`
- ✅ `getDataForDocument retourne toutes les données`

## 📊 Avant/Après

### Avant
- ❌ Styles CSS dupliqués dans chaque template
- ❌ Logo commenté et non affiché
- ❌ Couleurs incohérentes entre documents
- ❌ Code répétitif pour en-tête, client, footer
- ❌ Difficile de maintenir la cohérence
- ❌ Template `signed-devis` avec design complètement différent

### Après
- ✅ Styles partagés via `partials/styles.blade.php`
- ✅ Logo affiché sur tous les PDFs
- ✅ Couleurs cohérentes et configurables
- ✅ Composants réutilisables (header, client-info, footer)
- ✅ Facile à maintenir et à faire évoluer
- ✅ Tous les templates suivent le même design
- ✅ Configuration centralisée dans `config/branding.php`
- ✅ Helper pour simplifier l'utilisation
- ✅ Tests unitaires pour garantir la stabilité

## 📚 Documentation supplémentaire

Consultez `resources/views/pdf/README.md` pour :
- Guide complet d'utilisation
- Liste des classes CSS disponibles
- Bonnes pratiques
- Limitations de DomPDF

## 🎯 Avantages

1. **Cohérence** : Tous les PDFs ont le même look & feel
2. **Maintenabilité** : Un seul endroit pour modifier le branding
3. **Réutilisabilité** : Composants partagés entre templates
4. **Testabilité** : Tests unitaires pour BrandingHelper
5. **Flexibilité** : Facile de créer de nouveaux templates
6. **Configuration** : Couleurs et images centralisées
7. **Documentation** : Guide complet pour les développeurs

## 🚀 Prochaines étapes possibles

- [ ] Ajouter d'autres couleurs thématiques
- [ ] Créer des variantes de templates (compact, détaillé)
- [ ] Ajouter un watermark optionnel
- [ ] Internationalisation (i18n) des templates
- [ ] Mode sombre pour certains documents
- [ ] Générateur de preview en temps réel
