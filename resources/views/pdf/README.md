# Branding des PDFs Samaritain

Ce dossier contient tous les templates PDF de l'application Samaritain. Tous les PDFs suivent un branding cohérent pour une expérience utilisateur uniforme.

## 🎨 Charte graphique

### Couleurs principales

- **Orange** : `#f47920` - Couleur principale pour devis, factures, comptes-rendus
- **Vert** : `#10b981` - Pour les documents signés et attestations de succès
- **Rouge** : `#dc2626` - Pour les alertes ou attestations spécifiques (optionnel)

### Couleurs d'arrière-plan

- **Orange clair** : `#fff8f0` - Arrière-plan des en-têtes de tableaux (orange)
- **Vert clair** : `#f0fdf4` - Arrière-plan des en-têtes de tableaux (vert)
- **Rouge clair** : `#fef2f2` - Arrière-plan des en-têtes de tableaux (rouge)

### Images de branding

- **Logo** : `public/images/logo-samaritain.png`
- **Vague d'en-tête** : `public/images/header-wave.png`

## 📁 Structure des templates

### Templates principaux

1. **`devis-template.blade.php`** - Template pour les devis
2. **`facture-template.blade.php`** - Template pour les factures
3. **`attestation-template.blade.php`** - Template pour les attestations
4. **`compte-rendu-template.blade.php`** - Template pour les comptes-rendus d'intervention
5. **`signed-devis.blade.php`** - Template pour les devis signés électroniquement

### Composants partagés (partials)

Tous les templates utilisent des composants partagés situés dans `partials/` :

- **`styles.blade.php`** - Styles CSS communs à tous les PDFs
- **`header.blade.php`** - En-tête avec vague et logo
- **`client-info.blade.php`** - Bloc d'informations client et artisan
- **`footer.blade.php`** - Pied de page standard

## 🔧 Utilisation

### Créer un nouveau template PDF

Pour créer un nouveau template PDF qui respecte le branding :

```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Document</title>
    @include('pdf.partials.styles', [
        'accentColor' => '#f47920',      // Couleur principale
        'accentBgColor' => '#fff8f0'     // Couleur de fond
    ])
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        @include('pdf.partials.header', [
            'title' => 'Mon Document',
            'waveBase64' => $waveBase64,
            'logoBase64' => $logoBase64
        ])

        <!-- Informations client -->
        @include('pdf.partials.client-info', [
            'client' => $client,
            'artisan' => $artisan
        ])

        <!-- Contenu spécifique... -->

        <!-- Pied de page -->
        @include('pdf.partials.footer', [
            'message' => 'Merci de votre confiance.'
        ])
    </div>
</body>
</html>
```

### Variables requises dans le contrôleur

Pour que les templates fonctionnent correctement, assurez-vous de passer ces variables :

```php
// Images encodées en base64
$logoPath = public_path('images/logo-samaritain.png');
$wavePath = public_path('images/header-wave.png');

$logoBase64 = file_exists($logoPath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
    : null;

$waveBase64 = file_exists($wavePath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($wavePath))
    : null;

// Dans la vue
$html = view('pdf.mon-template', compact(
    'document',
    'client',
    'artisan',
    'logoBase64',
    'waveBase64'
))->render();
```

## 🎨 Classes CSS disponibles

Les styles partagés fournissent ces classes CSS :

### Conteneurs
- `.container` - Conteneur principal (800px max-width)
- `.table-wrapper` - Wrapper pour tableaux avec padding

### En-tête
- `.header-wave` - Conteneur de la vague
- `.header-content` - Contenu positionné sur la vague
- `.document-title` - Titre du document (blanc, 20px, gras)
- `.logo-block` - Bloc du logo

### Tableaux
- `table` - Tableau avec bordures arrondies et couleur d'accent
- `table th` - En-têtes de tableau
- `table td` - Cellules de tableau
- `.text-right` - Alignement à droite
- `.text-center` - Alignement centré

### Totaux
- `.totals-wrapper` - Wrapper des totaux
- `.totals-table` - Tableau des sous-totaux
- `.total-band` - Bandeau coloré du total final

### Informations
- `.client-block` - Bloc d'informations client
- `.description-block` - Bloc de description
- `.payment-info` - Informations de paiement
- `.signature-box` - Cadre pour signature

### Autres
- `.status-badge` - Badge de statut (vert)
- `.footer` - Pied de page
- `.photos-wrapper` - Wrapper pour photos (compte-rendu)
- `.photos-grid` - Grille de photos
- `.photo-item` - Item de photo

## 📋 Bonnes pratiques

1. **Toujours utiliser les composants partagés** pour maintenir la cohérence
2. **Encoder les images en base64** pour éviter les problèmes de chemins avec DomPDF
3. **Utiliser les couleurs de la charte** pour tous les nouveaux éléments
4. **Tester avec DomPDF** car certains CSS ne sont pas supportés (flexbox limité, grid non supporté)
5. **Garder les styles inline ou dans `<style>`** - pas de fichiers CSS externes
6. **Vérifier la compatibilité** - DomPDF supporte CSS 2.1 + quelques propriétés CSS3

## 🔄 Mise à jour du branding

Si vous devez changer les couleurs du branding :

1. Modifier `resources/views/pdf/partials/styles.blade.php`
2. Mettre à jour les appels `@include('pdf.partials.styles')` avec les nouvelles couleurs
3. Tester tous les templates PDF pour vérifier la cohérence

## 📝 Services associés

- **`app/Services/DevisPdfGenerator.php`** - Génération des devis
- **`app/Services/DocumentPdfGenerator.php`** - Génération générique de documents
- **`app/Services/PdfSignatureService.php`** - Génération des documents signés

Tous ces services encodent automatiquement les images de branding en base64.
