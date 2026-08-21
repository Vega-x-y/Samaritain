# Plan de réalisation

## Objectif
Traiter les 20 points fonctionnels et UX de l'application en conservant les conventions Laravel/Livewire existantes, avec une validation ciblée après chaque lot.

## Ordre d'exécution

### Phase 0 - Repérage et diagnostic
- Identifier les écrans, composants Livewire, routes, modèles et services concernés.
- Reproduire les bugs avant modification lorsque cela est possible.
- Vérifier les versions des dépendances utiles, notamment Livewire, paiements et outils frontend.
- Définir les tests ou vérifications nécessaires pour chaque lot.

### Phase 1 - Parcelle, contact et interactions
Points concernés : 1, 3, 4, 5, 6a, 6b, 7, 8, 9, 10.
- Corriger le bouton « Nous contacter » et l'envoi de message depuis une parcelle.
- Retirer l'option « Voir sur la carte » si elle n'est plus attendue.
- Ajuster la barre de filtres et intégrer l'image de fond prévue pour la partie parcelle.
- Rendre le bouton de like fonctionnel et persistant.
- Finaliser le fonctionnement du pass visite côté parcelle.
- Afficher correctement la localisation de la parcelle.
- Remplacer la phrase par défaut du formulaire de contact et harmoniser le vocabulaire autour de l'affichage/envoi de message.
- Corriger le champ « viabilisé ».

Validation : parcours parcelle sur desktop et mobile, formulaire valide/invalide, persistance du like, pass visite et affichage des données.

### Phase 2 - Dashboards et autorisations
Points concernés : 2, 11, 12, 14, 16, 18.
- Harmoniser les noms des documents dans le Dashboard artisan.
- Rendre les cartes du Dashboard client dynamiques.
- Ajouter et vérifier le pass visite dans le Dashboard client.
- Corriger les autorisations d'accès aux documents du Dashboard propriétaire.
- Rendre le Dashboard artisan responsive sur mobile.
- Afficher la photo de l'artisan dans son dashboard.

Validation : accès par rôle (client, artisan, propriétaire), données réelles, documents autorisés/interdits, affichage mobile.

### Phase 3 - Paiements
Points concernés : 13, 20.
- Diagnostiquer puis corriger le bug PawaPay avec les statuts, erreurs et callbacks concernés.
- Intégrer le système de paiement dans la partie artisan en réutilisant le service de paiement existant.
- Vérifier la sécurité des retours de paiement et l'idempotence des traitements.

Validation : scénarios sandbox, succès, échec, statut en attente, callback répété et absence de fuite d'informations sensibles.

### Phase 4 - Responsive et documents métier
Points concernés : 15, 17, 19.
- Corriger la responsivité mobile de la messagerie client.
- Corriger la dernière phrase affichée sur le devis.
- Rendre le bouton de création de bail utilisable sur mobile.

Validation : vues mobiles étroites et larges, navigation clavier/tactile, aucun débordement ni action masquée.

### Phase 5 - Validation finale
- Exécuter les tests ciblés après chaque lot puis la suite pertinente.
- Vérifier les erreurs navigateur et les logs récents.
- Vérifier les régressions sur les rôles et les parcours parcelle, messagerie, dashboards et paiement.
- Mettre à jour `todo.md` avec le statut réel, les tests exécutés et les éventuels blocages.

## Règles de suivi
- `À faire` : aucune modification commencée.
- `En cours` : diagnostic ou implémentation active.
- `Bloqué` : dépendance ou information manquante, indiquée dans les notes.
- `À valider` : implémentation terminée, validation fonctionnelle encore nécessaire.
- `Terminé` : implémentation et validation effectuées.

## Dépendances importantes
- Les autorisations et les données existantes doivent être comprises avant de rendre les dashboards dynamiques.
- Le service PawaPay existant doit être diagnostiqué avant toute nouvelle intégration artisan.
- Les changements de formulaire, de messagerie et de pass visite doivent être validés avec les rôles concernés.
