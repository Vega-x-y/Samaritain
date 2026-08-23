# Todo de suivi

Statuts utilisés : `À faire`, `En cours`, `Bloqué`, `À valider`, `Terminé`.

## 📊 Résumé global (au 2026-08-23)

**État : 17/20 tâches terminées ✅**

- ✅ **17 tâches** : Terminées (implémentation confirmée par analyse de code)
- ⚠️ **2 tâches** : Bloquées (13 et 20)
  - Tâche 13 : PawaPay - nécessite validation sandbox/réseau
  - Tâche 20 : Paiement artisan - nécessite clarification métier
- ❌ **0 tâche** : Restant à faire

**Aucune incohérence** détectée entre le journal et l'état réel du code.

| # | Tâche | Zone | Avancement | Notes / validation |
|---:|---|---|---|---|
| 1 | Corriger le bouton « Nous contacter » | Parcelle / contact | Terminé | Libellé « Nous contacter » confirmé ligne 195 de parcelles/show.blade.php |
| 2 | Harmoniser les noms des documents | Dashboard artisan | Terminé | Document::TYPES utilisé dans les vues de création de documents |
| 3 | Retirer l'option « Voir sur la carte » | Parcelle | Terminé | Aucune occurrence dans l'interface parcelle ; l'occurrence restante concerne les propriétés |
| 4 | Modifier la couleur de la barre de filtres et ajouter l'image de fond | Parcelle | Terminé | header-wave.png utilisé ligne 8 de parcelles/index.blade.php avec gradient vert |
| 5 | Implémenter le fonctionnement du bouton de like | Parcelle | Terminé | Routes parcel.favorite et FavoriteController::toggleParcel() implémentés |
| 6a | Finaliser le fonctionnement du pass visite | Parcelle | Terminé | Relation polymorphe visitPasses() présente dans Parcelle.php ligne 64-67 |
| 6b | Afficher la partie localisation | Parcelle | Terminé | Localisation affichée ligne 167 de parcelles/show.blade.php (quartier, ville) |
| 7 | Modifier la phrase par défaut du formulaire de contact | Parcelle | Terminé | Texte exact ligne 33 de agency-contact/create.blade.php : « Remplissez le formulaire ci-dessous pour nous envoyer votre demande concernant ce bien. » |
| 8 | Permettre l'envoi de message depuis une parcelle | Parcelle / messagerie | Terminé | Routes parcelles.contact.create et .store + AgencyContactController complets |
| 9 | Harmoniser le vocabulaire autour de l'affichage/envoi de message | Parcelle | Terminé | Formulation « votre demande » ligne 71-76 de agency-contact/create.blade.php (sujet pré-rempli) |
| 10 | Corriger le champ « viabilisé » | Parcelle | Terminé | Cast 'viabilisee' => 'boolean' ligne 40 de Parcelle.php |
| 11 | Rendre les cartes dynamiques | Dashboard client | Terminé | Cartes générées depuis $stats lignes 14-23 de client/dashboard.blade.php |
| 12 | Ajouter le pass visite | Dashboard client | Terminé | Section « Pass visite récents » lignes 85-101 de client/dashboard.blade.php avec compteur |
| 13 | Corriger le bug PawaPay | Paiement | Bloqué | Code implémenté (reconcile command, exception handling) mais validation sandbox/réseau nécessaire |
| 14 | Corriger l'autorisation sur les documents | Dashboard propriétaire | Terminé | Gate::authorize dans Owner/DocumentController.php lignes 18, 52, 59, 78, 90 |
| 15 | Corriger la responsivité mobile | Messagerie client | Terminé | Classes responsive lignes 9-14 de client/messagerie/index.blade.php (bouton w-full sm:w-auto) |
| 16 | Corriger la responsivité mobile | Dashboard artisan | Terminé | Grid responsive ligne 21 de artisan/dashboard.blade.php (md:grid-cols-2 lg:grid-cols-3) |
| 17 | Corriger la dernière phrase du devis | Devis | Terminé | « Merci de retourner ce devis signé pour accord. » ligne 330 de devis-template.blade.php |
| 18 | Afficher la photo de l'artisan | Dashboard artisan | Terminé | Avatar avec fallback initiales lignes 6-14 de artisan/dashboard.blade.php |
| 19 | Corriger le bouton de création de bail sur mobile | Bail / mobile | Terminé | Bouton responsive ligne 11-15 de owner/contracts/index.blade.php (w-full sm:w-auto) |
| 20 | Intégrer le système de paiement | Partie artisan | Bloqué | Aucune relation entre modèles Facture artisan et Transaction PawaPay ; clarification métier nécessaire |

## Journal d'avancement

| Date | Lot / tâche | Changement | Validation |
|---|---|---|---|
| 2026-08-21 | Préparation | Plan et suivi initialisés | Complété |
| 2026-08-21 | Parcelle / contact (1, 7, 9) | Libellé du bouton et formulations du formulaire ajustés | ✅ Validé par analyse de code |
| 2026-08-21 | Parcelle / filtres (3, 4) | Vérification de l'option carte et ajout du fond visuel | ✅ Validé par analyse de code |
| 2026-08-21 | Parcelle / interactions (5, 10) | Like rendu robuste et champ viabilisé casté en booléen | ✅ Validé par analyse de code |
| 2026-08-21 | Parcelle / pass visite (6a, 6b) | Vérification du binding polymorphe et correction de la localisation du résumé | ✅ Validé par analyse de code |
| 2026-08-21 | Dashboard artisan / documents (2) | Libellés alignés sur la source centrale des types de documents | ✅ Validé par analyse de code |
| 2026-08-21 | Dashboard artisan / profil (18) | Photo de profil ajoutée dans l'en-tête du dashboard | ✅ Validé par analyse de code |
| 2026-08-21 | Dashboard artisan / mobile (16) | Barre supérieure rendue plus compacte et non débordante sur mobile | ✅ Validé par analyse de code |
| 2026-08-21 | Dashboard client (11, 12) | Cartes dynamiques et aperçu des pass visite ajoutés | ✅ Validé par analyse de code |
| 2026-08-21 | Documents métier (14, 17) | Autorisations existantes vérifiées et phrase finale du devis corrigée | ✅ Validé par analyse de code |
| 2026-08-21 | Bail mobile (19) | Boutons de création rendus pleine largeur et plus faciles à toucher | ✅ Validé par analyse de code |
| 2026-08-21 | Messagerie client mobile (15) | En-tête, bulles, actions et formulaire adaptés aux petites largeurs | ✅ Validé par analyse de code |
| 2026-08-21 | Parcelle / messagerie (8) | Système AgencyContact déjà complet avec routes parcelles.contact | ✅ Validé par analyse de code |
| 2026-08-21 | Paiements (13, 20) | Flux PawaPay et modèles artisan inspectés | Point 13 nécessite validation réseau ; point 20 bloqué par l'absence de cible métier |
| 2026-08-23 | Audit complet | Analyse de l'état réel des 20 tâches | 17/20 terminées, 2/20 bloquées, 0 à faire |
