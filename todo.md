# Todo de suivi

Statuts utilisés : `À faire`, `En cours`, `Bloqué`, `À valider`, `Terminé`.

| # | Tâche | Zone | Avancement | Notes / validation |
|---:|---|---|---|---|
| 1 | Corriger le bouton « Nous contacter » | Parcelle / contact | À valider | Libellé mis à jour dans la fiche parcelle ; test bloqué par PHP indisponible |
| 2 | Harmoniser les noms des documents | Dashboard artisan | À valider | Libellés de création alignés sur `Document::TYPES` et espace final supprimé |
| 3 | Retirer l'option « Voir sur la carte » | Parcelle | Terminé | Aucune occurrence dans l'interface parcelle ; l'occurrence restante concerne les propriétés |
| 4 | Modifier la couleur de la barre de filtres et ajouter l'image de fond | Parcelle | À valider | Image `header-wave.png` ajoutée au hero et surface de filtres ajustée ; validation visuelle restante |
| 5 | Implémenter le fonctionnement du bouton de like | Parcelle | À valider | Endpoint existant conservé ; clic isolé, état de chargement et gestion d'erreur ajoutés |
| 6a | Finaliser le fonctionnement du pass visite | Parcelle | À valider | Route polymorphe et transmission `parcelle_id` déjà en place ; scénario parcelle restant |
| 6b | Afficher la partie localisation | Parcelle | À valider | Localisation quartier/ville affichée sur la fiche et le résumé du pass visite |
| 7 | Modifier la phrase par défaut du formulaire de contact | Parcelle | À valider | Texte d'introduction et sujet par défaut mis à jour ; test bloqué par PHP indisponible |
| 8 | Permettre l'envoi de message depuis une parcelle | Parcelle / messagerie | À faire | |
| 9 | Harmoniser le vocabulaire autour de l'affichage/envoi de message | Parcelle | À valider | Formulation « votre demande » appliquée au formulaire ; validation fonctionnelle restante |
| 10 | Corriger le champ « viabilisé » | Parcelle | À valider | Cast booléen réactivé dans le modèle Parcelle ; test Laravel restant |
| 11 | Rendre les cartes dynamiques | Dashboard client | À valider | Cartes configurées depuis les statistiques réelles, sans classes Tailwind interpolées |
| 12 | Ajouter le pass visite | Dashboard client | À valider | Compteur et aperçu des trois derniers pass de l'utilisateur ajoutés |
| 13 | Corriger le bug PawaPay | Paiement | Bloqué | Flux et tests existants vérifiés ; correction réseau transversale à valider avec PHP/sandbox |
| 14 | Corriger l'autorisation sur les documents | Dashboard propriétaire | À valider | Contrôleur et policy limitent déjà consultation/téléchargement/suppression au propriétaire ; tests existants |
| 15 | Corriger la responsivité mobile | Messagerie client | À valider | Actions mobiles visibles, messages et formulaire d'envoi adaptés aux petites largeurs |
| 16 | Corriger la responsivité mobile | Dashboard artisan | À valider | Barre supérieure adaptée aux petites largeurs |
| 17 | Corriger la dernière phrase du devis | Devis | À valider | « Merci de retourner ce devis signé pour accord. » |
| 18 | Afficher la photo de l'artisan | Dashboard artisan | À valider | Avatar ajouté dans l'en-tête avec fallback par initiales |
| 19 | Corriger le bouton de création de bail sur mobile | Bail / mobile | À valider | Boutons pleine largeur et hauteur tactile minimale sur mobile |
| 20 | Intégrer le système de paiement | Partie artisan | Bloqué | Aucun lien actuel entre factures artisan et transactions PawaPay ; cible métier à préciser |

## Journal d'avancement

| Date | Lot / tâche | Changement | Validation |
|---|---|---|---|
| 2026-08-21 | Préparation | Plan et suivi initialisés | À compléter |
| 2026-08-21 | Parcelle / contact (1, 7, 9) | Libellé du bouton et formulations du formulaire ajustés | À valider ; PHP indisponible dans le terminal |
| 2026-08-21 | Parcelle / filtres (3, 4) | Vérification de l'option carte et ajout du fond visuel | Point 3 terminé ; point 4 à valider visuellement |
| 2026-08-21 | Parcelle / interactions (5, 10) | Like rendu robuste et champ viabilisé casté en booléen | À valider ; PHP indisponible dans le terminal |
| 2026-08-21 | Parcelle / pass visite (6a, 6b) | Vérification du binding polymorphe et correction de la localisation du résumé | À valider ; PHP indisponible dans le terminal |
| 2026-08-21 | Dashboard artisan / documents (2) | Libellés alignés sur la source centrale des types de documents | À valider visuellement ; PHP indisponible dans le terminal |
| 2026-08-21 | Dashboard artisan / profil (18) | Photo de profil ajoutée dans l'en-tête du dashboard | À valider visuellement ; PHP indisponible dans le terminal |
| 2026-08-21 | Dashboard artisan / mobile (16) | Barre supérieure rendue plus compacte et non débordante sur mobile | À valider visuellement ; PHP indisponible dans le terminal |
| 2026-08-21 | Dashboard client (11, 12) | Cartes dynamiques et aperçu des pass visite ajoutés | À valider ; PHP indisponible dans le terminal |
| 2026-08-21 | Documents métier (14, 17) | Autorisations existantes vérifiées et phrase finale du devis corrigée | Point 14 à valider par tests ; point 17 à valider par génération PDF |
| 2026-08-21 | Bail mobile (19) | Boutons de création rendus pleine largeur et plus faciles à toucher | À valider visuellement ; PHP indisponible dans le terminal |
| 2026-08-21 | Messagerie client mobile (15) | En-tête, bulles, actions et formulaire adaptés aux petites largeurs | À valider visuellement ; PHP indisponible dans le terminal |
| 2026-08-21 | Paiements (13, 20) | Flux PawaPay et modèles artisan inspectés | Point 13 nécessite validation réseau ; point 20 bloqué par l'absence de cible métier et de relation facture/transaction |
