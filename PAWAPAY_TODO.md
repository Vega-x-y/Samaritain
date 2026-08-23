# PawaPay - TODO List

Liste des tâches pour finaliser l'intégration PawaPay dans l'application.

## ✅ Fait (Intégration de base)

- [x] Configuration centralisée (`config/pawapay.php`)
- [x] Enums pour statuts et types
- [x] DTOs pour requêtes typées
- [x] Service PawaPay complet
- [x] Controller de callbacks
- [x] Route webhook configurée
- [x] Modèle Transaction amélioré
- [x] Migration pour `refund_id`
- [x] Tests complets (52 tests)
- [x] Documentation exhaustive
- [x] Exemples de code

## 🔨 À faire - Configuration

- [ ] **Copier variables d'environnement**
  - [ ] Copier `.env.pawapay.example` dans `.env`
  - [ ] Remplir `PAWAPAY_TOKEN` avec token sandbox
  - [ ] Vérifier `PAWAPAY_DEFAULT_CURRENCY` (CDF/XAF/etc.)
  - [ ] Configurer `PAWAPAY_CALLBACK_URL`

- [ ] **Ajuster les providers**
  - [ ] Dans `config/pawapay.php`, mettre à jour la liste des providers selon le pays ciblé
  - [ ] Tester avec `php artisan tinker` : `app(\App\Services\PawapayService::class)->getActiveConfiguration()`

- [ ] **Lancer les migrations**
  ```bash
  php artisan migrate
  ```

- [ ] **Configurer le callback webhook**
  - [ ] En dev: exposer avec ngrok (`ngrok http 8000`)
  - [ ] Dashboard PawaPay → System Configuration → Callback URLs
  - [ ] Ajouter l'URL: `https://votre-url.com/webhooks/pawapay/callback`
  - [ ] Tester la connexion

## 🧪 À faire - Tests

- [ ] **Lancer les tests**
  ```bash
  php artisan test --filter=Pawapay --compact
  ```
  - [ ] Tous les tests passent ✅

- [ ] **Tester manuellement un dépôt**
  - [ ] Créer un utilisateur de test
  - [ ] Utiliser un numéro sandbox (ex: `242064000001` pour Congo-Brazzaville)
  - [ ] Initier un dépôt via tinker (voir `PAWAPAY_SETUP_GUIDE.md`)
  - [ ] Vérifier le callback reçu
  - [ ] Vérifier que la transaction passe à COMPLETED

- [ ] **Tester les cas d'échec**
  - [ ] Numéro sandbox échec (ex: `242064000100`)
  - [ ] Vérifier que la transaction passe à FAILED
  - [ ] Vérifier le `failure_reason`

## 🎨 À faire - Interface utilisateur

### Paiement des loyers (Tenants)

- [ ] **Page de sélection du moyen de paiement**
  - [ ] Livewire component pour afficher les providers disponibles
  - [ ] Utiliser `PawapayService::availableProviders()` ou `getActiveConfiguration()`
  - [ ] Afficher logos des opérateurs
  - [ ] Formulaire numéro de téléphone avec validation

- [ ] **Initiation du paiement**
  - [ ] Créer Transaction en PENDING
  - [ ] Appeler `PawapayService::initiateDeposit()`
  - [ ] Gérer ACCEPTED → page d'attente
  - [ ] Gérer REJECTED → message d'erreur clair

- [ ] **Page d'attente/statut**
  - [ ] Polling ou événements temps réel (Livewire polling)
  - [ ] Afficher statut actuel (PENDING → SUBMITTED → COMPLETED)
  - [ ] Redirection auto vers confirmation si COMPLETED
  - [ ] Message d'erreur si FAILED avec raison

- [ ] **Page de confirmation**
  - [ ] Afficher reçu de paiement
  - [ ] Montant, date, référence
  - [ ] Lien vers PDF ou impression

### Option alternative: Page hébergée

- [ ] **Bouton "Payer avec PawaPay"**
  - [ ] Appeler `PawapayService::createPaymentPage()`
  - [ ] Rediriger vers `redirectUrl`
  - [ ] Gérer le retour sur `PAWAPAY_RETURN_URL`
  - [ ] Toujours vérifier le statut final (pas juste le retour!)

### Dashboard admin

- [ ] **Liste des transactions**
  - [ ] Table Livewire avec filtres (status, type, provider)
  - [ ] Colonnes: Date, User, Type, Montant, Provider, Statut
  - [ ] Actions: Voir détails, Resend callback, Refund

- [ ] **Détails d'une transaction**
  - [ ] Toutes les infos (raw_response)
  - [ ] Timeline des changements de statut
  - [ ] Bouton "Vérifier statut" (polling manuel)
  - [ ] Bouton "Renvoyer callback"
  - [ ] Bouton "Rembourser" si COMPLETED

- [ ] **Métriques**
  - [ ] Total collecté par période
  - [ ] Taux de succès par provider
  - [ ] Montant moyen par transaction
  - [ ] Graphique des transactions par jour

### Payouts (Owner → Tenant)

- [ ] **Page d'envoi d'argent**
  - [ ] Formulaire: destinataire, montant, numéro
  - [ ] Prédiction provider automatique
  - [ ] Confirmation avant envoi
  - [ ] Appeler `PawapayService::initiatePayout()`

- [ ] **Gestion des payouts ENQUEUED**
  - [ ] Afficher statut "En file d'attente"
  - [ ] Bouton "Annuler" si encore ENQUEUED
  - [ ] Notification quand payout traité

### Refunds

- [ ] **Bouton "Rembourser" sur transaction COMPLETED**
  - [ ] Modal de confirmation
  - [ ] Montant total ou partiel
  - [ ] Appeler `PawapayService::initiateRefund()`
  - [ ] Créer nouvelle Transaction type REFUND
  - [ ] Suivre le statut

## 📧 À faire - Notifications

- [ ] **Email de confirmation de paiement**
  - [ ] Écouter événement ou callback
  - [ ] Mail class `PaymentCompletedMail`
  - [ ] Inclure reçu PDF attaché

- [ ] **Email d'échec de paiement**
  - [ ] Mail class `PaymentFailedMail`
  - [ ] Expliquer la raison (failure_reason)
  - [ ] Proposer de réessayer

- [ ] **Notifications in-app**
  - [ ] Notification Laravel pour COMPLETED
  - [ ] Notification Laravel pour FAILED
  - [ ] Badge de notifications

## 🔐 À faire - Sécurité

- [ ] **Production uniquement**
  - [ ] Activer `PAWAPAY_VERIFY_CALLBACK_SIGNATURE=true`
  - [ ] Générer paire de clés (RSA)
  - [ ] Uploader clé publique dans PawaPay Dashboard
  - [ ] Implémenter vérification dans `PawapayCallbackController::verifySignature()`

- [ ] **Rate limiting**
  - [ ] Limiter les tentatives de paiement par user
  - [ ] Throttle sur route callback (optionnel)

- [ ] **Validation stricte**
  - [ ] FormRequest pour initiation de paiement
  - [ ] Règles de validation (montant min/max, provider valide)

## 📊 À faire - Monitoring & Logs

- [ ] **Dashboard de monitoring**
  - [ ] Nombre de transactions par statut (temps réel)
  - [ ] Alertes sur taux d'échec > seuil
  - [ ] Log des temps de réponse PawaPay API

- [ ] **Logs structurés**
  - [ ] Contexte utilisateur dans tous les logs
  - [ ] Tags par type de transaction
  - [ ] Faciliter le debug avec trace_id

- [ ] **Rapports financiers**
  - [ ] Export CSV des transactions
  - [ ] Rapprochement avec dashboard PawaPay
  - [ ] Calcul des frais (si applicable)

## 🚀 À faire - Optimisations

- [ ] **Cache**
  - [ ] Cacher `getActiveConfiguration()` (TTL 1h)
  - [ ] Cacher `getAvailability()` (TTL 5min)
  - [ ] Invalider cache lors de changement config

- [ ] **Queues**
  - [ ] Queuer l'envoi d'emails
  - [ ] Queuer le traitement lourd dans callbacks
  - [ ] Job pour vérifier statut des transactions PENDING > 15min

- [ ] **Bulk operations**
  - [ ] Interface pour bulk payouts
  - [ ] Utiliser `POST /v2/payouts/bulk`
  - [ ] Uploader CSV de destinataires

## 🧹 À faire - Nettoyage

- [ ] **Supprimer ancien code**
  - [ ] Si l'ancien `PawapayService` est complètement remplacé
  - [ ] Vérifier qu'aucune référence à l'ancien code
  - [ ] Supprimer vieilles migrations inutiles (si applicable)

- [ ] **Uniformiser les conventions**
  - [ ] Remplacer toutes les strings 'deposit'/'payout' par les enums
  - [ ] Ajouter les enums dans les factories
  - [ ] Ajouter les enums dans les seeders

## 📚 À faire - Documentation

- [ ] **Mettre à jour README principal**
  - [ ] Section "Paiements Mobile Money"
  - [ ] Lien vers `PAWAPAY_INTEGRATION.md`

- [ ] **Guide utilisateur**
  - [ ] Comment payer son loyer (pour tenants)
  - [ ] Comment envoyer de l'argent (pour owners)
  - [ ] FAQ des erreurs courantes

- [ ] **Guide développeur**
  - [ ] Ajouter nouveaux providers
  - [ ] Personnaliser les messages
  - [ ] Gérer les cas spéciaux

## 🏁 Checklist production

- [ ] **Sandbox complètement testé**
  - [ ] Au moins 10 paiements test réussis
  - [ ] Au moins 5 paiements test échoués (intentionnels)
  - [ ] Au moins 1 refund test
  - [ ] Callbacks tous reçus et traités

- [ ] **KYC PawaPay complété**
  - [ ] Documents fournis
  - [ ] Compte approuvé
  - [ ] Accès production activé

- [ ] **Token production**
  - [ ] Généré depuis dashboard production
  - [ ] Configuré dans `.env` production
  - [ ] `PAWAPAY_BASE_URL` changé pour `https://api.pawapay.io`

- [ ] **Callback production**
  - [ ] URL HTTPS configurée
  - [ ] Testée depuis dashboard PawaPay
  - [ ] Logs de production actifs

- [ ] **Monitoring production**
  - [ ] Sentry/Bugsnag configuré
  - [ ] Alertes emails sur erreurs
  - [ ] Dashboard métriques actif

- [ ] **Backup plan**
  - [ ] Procédure si PawaPay down
  - [ ] Alternative de paiement (si applicable)
  - [ ] Support contact ready

## 🆘 Ressources

- 📖 Documentation: `PAWAPAY_INTEGRATION.md`
- 🚀 Setup: `PAWAPAY_SETUP_GUIDE.md`
- 🏗️ Architecture: `PAWAPAY_ARCHITECTURE.md`
- 💻 Exemples: `app/Examples/PawapayUsageExample.php`
- 🧪 Tests: `tests/Feature/Pawapay*Test.php`
- 🌐 PawaPay Docs: https://docs.pawapay.io/v2/docs/welcome
- 🔧 PawaPay Dashboard: https://dashboard.sandbox.pawapay.io

---

**Cochez les tâches au fur et à mesure ! ✅**
