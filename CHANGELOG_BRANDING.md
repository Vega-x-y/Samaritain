# Changelog - Branding PDF Unifié

## [1.0.0] - 2026-08-23

### ✨ Nouveautés

#### 🎨 Système de branding unifié
- Création d'un système de branding cohérent pour tous les PDFs de l'application
- Configuration centralisée des couleurs et assets dans `config/branding.php`
- Helper `BrandingHelper` pour faciliter l'utilisation du branding dans les services

#### 📦 Composants réutilisables
- **`partials/styles.blade.php`** : Styles CSS communs à tous les PDFs
- **`partials/header.blade.php`** : En-tête avec vague et logo
- **`partials/client-info.blade.php`** : Bloc d'informations client/artisan
- **`partials/footer.blade.php`** : Pied de page standard

### 🔄 Modifications

#### Templates PDF mis à jour
Tous les templates ont été refactorisés pour utiliser les composants partagés :

1. **`devis-template.blade.php`**
   - Utilise maintenant `@include('pdf.partials.styles')`
   - Utilise `@include('pdf.partials.header')`
   - Utilise `@include('pdf.partials.client-info')`
   - Utilise `@include('pdf.partials.footer')`
   - Logo maintenant affiché (était commenté)

2. **`facture-template.blade.php`**
   - Même refactorisation que le devis
   - Logo maintenant affiché

3. **`attestation-template.blade.php`**
   - Refactorisé avec composants partagés
   - Couleur harmonisée avec les autres documents (orange #f47920)
   - Logo maintenant affiché

4. **`compte-rendu-template.blade.php`**
   - Refactorisé avec composants partagés
   - Conserve sa fonctionnalité de grille de photos
   - Logo maintenant affiché

5. **`signed-devis.blade.php`**
   - Complètement redesigné pour suivre le branding Samaritain
   - Ajout de l'en-tête avec vague et logo
   - Badge de statut "SIGNÉ" vert cohérent
   - Section signature améliorée

#### Services mis à jour

1. **`app/Services/DevisPdfGenerator.php`**
   - Utilise `BrandingHelper::getEncodedImages()` au lieu de code dupliqué
   - Code simplifié et plus maintenable

2. **`app/Services/DocumentPdfGenerator.php`**
   - Utilise `BrandingHelper::getEncodedImages()`
   - Cohérence avec les autres services

3. **`app/Services/PdfSignatureService.php`**
   - Utilise `BrandingHelper::getEncodedImages()`
   - Passe maintenant les variables de branding à la vue

### 🆕 Nouveaux fichiers

#### Configuration
- **`config/branding.php`** : Configuration centralisée du branding
  - Couleurs principales (primary, success, danger)
  - Couleurs de fond
  - Chemins des images
  - Slogan
  - Mapping couleurs par type de document

#### Support
- **`app/Support/BrandingHelper.php`** : Classe helper pour le branding
  - `getEncodedImages()` : Récupère logo et vague en base64
  - `getColorForDocument(string $type)` : Couleur pour un type de document
  - `getBackgroundForDocument(string $type)` : Fond pour un type de document
  - `getDataForDocument(string $type)` : Toutes les données de branding

#### Tests
- **`tests/Unit/BrandingHelperTest.php`** : Tests unitaires pour BrandingHelper
  - 4 tests couvrant toutes les méthodes
  - 17 assertions au total
  - 100% de couverture du helper

#### Documentation
- **`resources/views/pdf/README.md`** : Guide complet d'utilisation des templates PDF
- **`BRANDING.md`** : Vue d'ensemble du système de branding
- **`CHANGELOG_BRANDING.md`** : Ce fichier

### 🎨 Charte graphique

#### Couleurs
- **Orange principal** : `#f47920` (devis, factures, attestations, comptes-rendus)
- **Vert succès** : `#10b981` (documents signés)
- **Rouge danger** : `#dc2626` (alertes, disponible mais non utilisé)

#### Fonds
- **Orange clair** : `#fff8f0`
- **Vert clair** : `#f0fdf4`
- **Rouge clair** : `#fef2f2`

#### Typographie
- Police : Arial, sans-serif
- Taille de base : 11px
- Ligne : 1.5
- Couleur texte : #333

### ✅ Tests

Tous les tests passent :
```bash
php artisan test --filter=BrandingHelperTest
# Tests: 4 passed (17 assertions)
```

### 📚 Migration

#### Avant
```php
// Dans chaque service, code dupliqué :
$logoPath = public_path('images/logo-samaritain.png');
$wavePath = public_path('images/header-wave.png');

$logoBase64 = file_exists($logoPath)
    ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
    : null;
// ... etc
```

#### Après
```php
// Code simplifié :
use App\Support\BrandingHelper;

$brandingData = BrandingHelper::getEncodedImages();
extract($brandingData); // $logoBase64, $waveBase64
```

### 🔧 Compatibilité

- ✅ Laravel 11.x
- ✅ DomPDF
- ✅ Pest PHP
- ✅ PHP 8.4

### 📝 Notes de migration

Aucune migration de données nécessaire. Les changements sont uniquement au niveau du code des templates et services.

Les PDFs existants ne sont pas affectés. Seuls les nouveaux PDFs générés utiliseront le nouveau branding.

### 🎯 Avantages

1. **Maintenance facilitée** : Un seul endroit pour modifier les styles
2. **Cohérence visuelle** : Tous les PDFs ont le même look & feel
3. **Code DRY** : Pas de duplication de code CSS ou HTML
4. **Testabilité** : Helper avec tests unitaires
5. **Flexibilité** : Facile d'ajouter de nouveaux templates
6. **Configuration** : Branding configurable via fichier de config

### 🚀 Prochaines étapes possibles

- [ ] Ajouter des templates pour d'autres types de documents
- [ ] Internationalisation (i18n) des templates
- [ ] Preview en temps réel lors de la création de documents
- [ ] Export en plusieurs formats (PDF, DOCX, HTML)
- [ ] Watermark configurable
- [ ] Thèmes multiples (clair/sombre)

---

## Comment utiliser

### Créer un nouveau template PDF

Voir `resources/views/pdf/README.md` pour le guide complet.

Exemple minimal :
```blade
@include('pdf.partials.styles', ['accentColor' => '#f47920'])
<!-- ... -->
@include('pdf.partials.header', ['title' => 'Mon Document', ...])
@include('pdf.partials.client-info', [...])
<!-- Votre contenu -->
@include('pdf.partials.footer')
```

### Modifier les couleurs du branding

Éditez `config/branding.php` :
```php
'colors' => [
    'primary' => '#votre-couleur',
    // ...
],
```

---

**Auteur** : Équipe Samaritain  
**Date** : 23 août 2026  
**Version** : 1.0.0
