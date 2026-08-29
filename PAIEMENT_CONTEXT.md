# Contexte : Système de paiement PawaPay (Samaritain)

> Document destiné à donner du contexte à un agent IA (ou un nouveau développeur) avant toute modification du système de paiement. Il décrit l'architecture, les flux, les conventions et les pièges connus.

---

## 1. Vue d'ensemble

L'application utilise **PawaPay** comme unique passerelle de paiement mobile money (dépôts et retraits). Tous les flux de paiement passent par **un seul contrôleur** : `App\Http\Controllers\TransactionsController` (au pluriel).

Les paiements servent 4 cas d'usage :

| Cas d'usage | Destinataire du crédit | Montant |
|---|---|---|
| Dépôt générique (recharge wallet) | Le wallet de l'utilisateur lui-même | Libre |
| Achat d'un pass visite (`VisitPass`) | **Aucun wallet** (génère QR + PDF) | Fixe = `visitPass->amount` |
| Paiement de loyer (`RentPayment`) | Le **wallet du propriétaire** (owner) | Fixe = `rentPayment->amount_due` |
| Retrait (payout) | Débit du wallet owner → numéro mobile money | Libre, max = solde |

## 2. Infrastructure

### Modèle `App\Models\Transaction`
- **UUID** en clé primaire : `transaction_id` (pas d'auto-increment).
- Casts enum : `type` (`App\Enums\TransactionType` : DEPOSIT, PAYOUT — **en MAJUSCULES**), `status` (`App\Enums\TransactionStatus`).
- Colonnes clés : `user_id`, `visit_pass_id` (nullable), `rent_payment_id` (nullable), `amount`, `provider`, `phone_number`, `deposit_id` / `payout_id` (UUID PawaPay), `raw_response` (JSON de la dernière réponse API/callback), `failure_reason` (accessor dérivé de `raw_response`).
- Statuts : `PENDING`, `ACCEPTED`, `COMPLETED`, `REJECTED`, `FAILED`, `PROCESSING`, `IN_RECONCILIATION`.

### Service `App\Services\PawapayService`
Point d'entrée unique vers l'API PawaPay v2 (HTTP client Laravel). Méthodes :
- `initiateDeposit(DepositRequest)` / `initiatePayout(PayoutRequest)` — DTOs dans `app/DataTransferObjects/Pawapay/`.
- `getDepositStatus()` / `getPayoutStatus()` — enveloppe `{ status: "FOUND", data: {...} }`.
- `getActiveConfiguration(?country, ?operationType)` — `GET /active-conf`, renvoie companyName, countries[] (displayName fr/en, prefix, flag, providers[] avec logo, currencies[].operationTypes[]).
- `providerBranding(countryConfig, operationType)` — extrait les providers OPERATIONAL avec `provider`, `displayName`, `logo`.
- `normalizePhoneNumber($phone)` — **PIÈGE CORRIGÉ** : garde le `0` de tête (`064567890` → `242064567890` pour le COG/dial 242). Le `0` fait partie du MSISDN congolais.
- Lance `App\Exceptions\PawaPayException` en cas d'échec HTTP.


### Wallets
- **Utilisateur** : crédité par `OwnerWalletService::creditDeposit()` pour les dépôts génériques (idempotent).
- **Propriétaire (owner)** : `App\Services\OwnerWalletService` — crédité lors du règlement d'un loyer ; les retraits (payouts) y sont débités. Colonnes `available_balance` etc.

### Services métier
- `App\Services\VisitPassService::handleSuccessfulPayment()` — génère QR + PDF du pass.
- `App\Services\RentPaymentService::handleSuccessfulPayment()` + `settle()` — marque le loyer payé et crédite le **wallet owner** (jamais le wallet du locataire).

### Configuration (`config/pawapay.php` + `config/services.php`)
- `PAWAPAY_MODE` (sandbox/production) choisit l'URL API.
- `PAWAPAY_CURRENCY` = `XAF`, `PAWAPAY_COUNTRY` = `COG`, `PAWAPAY_DIAL_CODE` = `242`.
- **⚠️ Unités : XAF = montants entiers (PawaPay `decimalsInAmount: NONE`). Ne JAMAIS multiplier par 100 ni formater avec décimales.** Les anciens `*100` / `/100` ont été supprimés partout.
- `services.pawapay.fee_percent` (5) pour les frais de retrait.


## 3. Flux de paiement (dépôt)

### Routes (`routes/web.php`)
```
GET  transactions/deposit                      transactions.deposit        → getDepositForm
POST transactions/deposit                      transactions.deposit        → initDeposit
GET  transactions/deposit/{t}/status           transactions.deposit.status → polling
GET  transactions/withdraw                     transactions.withdraw       → getWithdrawForm
POST transactions/withdraw                     transactions.withdraw       → initWithdraw
GET  transactions/withdraw/{t}/status          transactions.withdraw.status
GET  transactions/{t}/pending                  transactions.pending        → page d'attente
GET  transactions/{t}/status                   transactions.status         → vérification manuelle
POST webhooks/pawapay/callback                 pawapay.callback            → webhook (ne pas toucher)
```

### Déroulement d'un dépôt
1. **Formulaire** (`getDepositForm`) : récupère la config active PawaPay (`active-conf` filtrée pays + type d'opération). Si aucun provider → redirect back avec erreur. Contextes optionnels via query string : `?visit_pass={uuid}` (montant fixe) ou `?rent_payment={id}` (montant = `amount_due`, **authorité serveur**).
2. **Résolution du contexte** (`resolveVisitPass` / `resolveRentPayment`) : vérifie propriété, statut actif, non-payé. Sinon redirect avec message.
3. **POST** (`initDeposit`) : validation `amount`, `phone`, `provider`. Crée la `Transaction` en `PENDING`, appelle `initiateDeposit` :
4. **Finalisation** (dans la vérification de statut `getTransactionStatus` / `status`) :
   - `COMPLETED` → logique selon la transaction :
     - visit pass → `VisitPassService::handleSuccessfulPayment()`, **pas de crédit wallet** ;
     - rent payment → `settle()` crédite le **wallet owner** + `RentPaymentService::handleSuccessfulPayment()` ;
     - dépôt générique → `OwnerWalletService::creditDeposit()` (idempotent) sur le wallet de l'utilisateur.
   - `ACCEPTED`/`PROCESSING`/`IN_RECONCILIATION` → reste en attente ;
   - `REJECTED`/`FAILED` → stocke `raw_response`, affiche la raison.
5. **Callback** (`PawapayCallbackController`, `POST /webhooks/pawapay/callback`) : notification serveur-à-serveur (signature vérifiée). **Ne pas casser.** Le polling et le callback peuvent arriver ensemble → toute écriture finale doit être idempotente.

### Retrait (payout)
`getWithdrawForm` (min solde 1000) → `initWithdraw` → `PayoutRequest` → statut selon la même logique. Créé aussi depuis le dashboard owner (`Owner\PayoutController@store`).

   - réponse `ACCEPTED` → redirige vers `transactions.pending` (page qui **poll** `transactions.deposit.status` en JS) ;

## 4. UI des formulaires

Vues : `resources/views/transactions/{deposit-form,withdraw-form,status,pending}.blade.php`.

Composants :
- **`x-form.input`** (`resources/views/components/form/input.blade.php`) : props `prefix` (addon gauche, ex. `+242`) et `suffix` (addon droite, ex. `XAF`), plus `icon` (lucide). Sans addons, rendu arrondi classique.
- **`x-transactions.provider-picker`** (`resources/views/components/transactions/provider-picker.blade.php`) : composant anonyme, props `providers` (requis, liste du branding), `name` (défaut `provider`), `label` (défaut `Opérateur`). Cartes radio avec logo (fallback icône), sélection `peer-checked`.
- **`transactions/partials/branding-header.blade.php`** : bandeau drapeau + nom d'entreprise + pays. (Candidat à la conversion en composant.)
- **`x-btn`** vient du package **DistortedFusion Blade Components** (vues publiées dans `resources/views/vendor/blade-components`). **⚠️ Son `type` par défaut est `button`** → toujours ajouter `type="submit"` sur les boutons de soumission.

Données passées aux vues :
- `payment_config` = `active-conf → countries[0]` (contient `prefix`, `providers`…) — conservé pour compatibilité.
- `branding` = `buildBranding()` dans le contrôleur : `companyName`, `countryName`, `flag`, `prefix`, `providers` (via `providerBranding`). C'est la source d'affichage UI.

Dashboard owner : carte wallet (« Solde actuel », « Retraits en cours », 5 derniers payouts, bouton « Retirer » → `transactions.withdraw`) ; sidebar « Wallet & Retraits ». Vues `pages/owner/dashboard.blade.php`, `pages/owner/payouts/*` (⚠️ `status` est un **enum**, utiliser `->variant()` / `->label()`, jamais en offset de tableau).

## 5. Pièges connus (ne pas régresser)

1. **Montants XAF entiers** : pas de `*100`/`/100`, pas de décimales.
2. **`normalizePhoneNumber`** : le `0` de tête du numéro national doit être conservé (`064567890` → `242064567890`).
3. **`x-btn` type par défaut = `button`** → `type="submit"` obligatoire sur les boutons de soumission.
4. **Enums en MAJUSCULES** (`TransactionType::DEPOSIT`…).
5. **Idempotence** : `creditDeposit`, `handleSuccessfulPayment` et `settle` doivent être sûrs aux appels répétés (callback + polling peuvent arriver ensemble).
6. **Séparation wallet** : loyer → wallet **owner** ; dépôt générique → wallet de l'utilisateur ; pass visite → aucun wallet.
7. **Deux contrôleurs** : `TransactionsController` (pluriel, flux pawaPay) vs `TransactionController` (singulier, historique : vues tenant `tenant.transactions.*`, pages pending/status). Ne pas confondre.
8. **Webhook** (`POST /webhooks/pawapay/callback`) : ne pas modifier sans gérer la signature.
9. **`x-btn` dupliqué** : ne jamais recréer un composant `btn` dans `resources/views/components/` — il masquerait celui du package.

## 6. Tests

- Fichiers : `tests/Feature/TransactionsControllerTest.php`, `RentPaymentTest.php`, `OwnerDashboardTest.php`, `PawapayServiceTest.php`, `PawapayDepositTest.php`, `PayoutControllerTest.php`.
- Framework : Pest. Lancer : `php artisan test --compact` (filtrer avec `--filter=`).
- **⚠️ Environnement local sans driver PDO** (phpunit sur sqlite :memory:) : les tests doivent être exécutés dans un environnement compatible.
- Conventions : factories obligatoires, `php artisan make:test --pest NomDuTest`.

## 7. Ajouter un nouveau type de paiement (recette)

1. Colonne de liaison nullable sur `transactions` (ex. `xxx_id`) + migration.
2. `getDepositForm` : accepter le paramètre query et le résoudre via une méthode `resolveXxx()` (propriété + statut + non-payé → sinon redirect).
3. Formulaire : bloc montant fixe (champ `amount` hidden, jamais trusted du client — **authorité serveur**).
4. Finalisation (`COMPLETED`) : brancher la logique métier. Décider qui est crédité selon la table de la section 1.
5. Test Pest couvrant : résolution invalide, montant serveur, crédit cible, idempotence.
