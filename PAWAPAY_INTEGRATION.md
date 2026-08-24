# Intégration PawaPay - Documentation

Cette documentation décrit l'intégration complète de PawaPay (paiement Mobile Money) dans l'application Samaritain.

## Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture](#architecture)
3. [Configuration](#configuration)
4. [Utilisation côté backend](#utilisation-côté-backend)
5. [Utilisation côté frontend](#utilisation-côté-frontend)
6. [Webhooks & Callbacks](#webhooks--callbacks)
7. [Tests](#tests)
8. [Sandbox & Production](#sandbox--production)
9. [Dépannage](#dépannage)

---

## Vue d'ensemble

PawaPay est une API asynchrone de paiement Mobile Money couvrant 20+ pays africains. Cette intégration permet :

- **Dépôts (Deposits)** : Encaisser de l'argent d'un client
- **Retraits (Payouts)** : Envoyer de l'argent à un client
- **Remboursements (Refunds)** : Rembourser un dépôt complété
- **Page de paiement hébergée** : Rediriger vers une interface PawaPay
- **Callbacks/Webhooks** : Recevoir les mises à jour de statut en temps réel

### Fonctionnement asynchrone

PawaPay ne retourne **jamais** le statut final immédiatement. Le flux typique :

1. Vous initiez un dépôt → réponse `ACCEPTED` (pas encore payé)
2. Le client valide sur son téléphone (PIN)
3. PawaPay envoie un callback → statut `COMPLETED` ou `FAILED`

**Ne jamais considérer un paiement réussi tant que le statut final n'est pas `COMPLETED`.**

---

## Architecture

### Structure des fichiers

```
app/
├── DataTransferObjects/Pawapay/
│   ├── DepositRequest.php
│   ├── PayoutRequest.php
│   └── RefundRequest.php
├── Enums/
│   ├── TransactionStatus.php
│   └── TransactionType.php
├── Exceptions/
│   └── PawaPayException.php
├── Http/Controllers/
│   ├── PawapayCallbackController.php
│   └── TransactionController.php
├── Livewire/Payment/
│   ├── InitiateDeposit.php
│   ├── TransactionsList.php
│   └── TransactionStatus.php
├── Models/
│   └── Transaction.php
└── Services/
    └── PawapayService.php

config/
└── pawapay.php

database/migrations/
├── 2026_07_06_101538_create_transactions_table.php
├── 2026_08_12_000000_add_deposit_id_and_provider_to_transactions_table.php
├── 2026_08_14_163036_add_type_and_payout_id_to_transactions_table.php
└── 2026_08_22_234800_add_refund_id_to_transactions_table.php

resources/views/
├── livewire/payment/
│   ├── initiate-deposit.blade.php
│   ├── transactions-list.blade.php
│   └── transaction-status.blade.php
└── pages/tenant/transactions/
    ├── index.blade.php
    └── show.blade.php

routes/
└── web.php (routes tenant.transactions.* et pawapay.callback)

tests/Feature/
├── Livewire/Payment/
│   ├── TransactionsListTest.php
│   └── TransactionStatusTest.php
└── Payment/
    └── PawapayIntegrationTest.php
```

---

## Configuration

### 1. Variables d'environnement

Ajouter dans `.env` :

```env
# PawaPay Configuration
PAWAPAY_ENV=sandbox
PAWAPAY_SANDBOX_URL=https://api.sandbox.pawapay.io
PAWAPAY_LIVE_URL=https://api.pawapay.io
PAWAPAY_API_TOKEN=your_sandbox_token_here

# Callback signature verification (optionnel, recommandé en production)
PAWAPAY_CALLBACK_VERIFY_SIGNATURE=false
PAWAPAY_CALLBACK_PUBLIC_KEY=

# Paramètres par défaut
PAWAPAY_CURRENCY=XAF
PAWAPAY_COUNTRY=COG
PAWAPAY_DIAL_CODE=242

# URLs
PAWAPAY_CALLBACK_URL=https://votre-domaine.com/webhooks/pawapay/callback
PAWAPAY_RETURN_URL=https://votre-domaine.com/tenant/payments
```

### 2. Fournisseurs (Providers)

Les providers sont configurés dans `config/pawapay.php` :

```php
'providers' => [
    'MTN_MOMO_COG' => 'MTN Mobile Money',
    'AIRTEL_COG' => 'Airtel Money',
    // Ajouter d'autres providers selon votre compte PawaPay
],
```

**Important** : Utiliser `GET /v2/active-conf` pour obtenir la liste exacte des providers activés sur votre compte.

### 3. Configurer les callbacks dans le dashboard PawaPay

1. Se connecter sur https://dashboard.sandbox.pawapay.io (ou dashboard production)
2. Aller dans **System Configuration → Callback URLs**
3. Définir l'URL du callback : `https://votre-domaine.com/webhooks/pawapay/callback`
4. Activer les callbacks pour Deposits, Payouts, et Refunds

---

## Utilisation côté backend

### Service PawaPay

Le service `App\Services\PawapayService` expose toutes les méthodes de l'API PawaPay.

#### Initier un dépôt

```php
use App\Services\PawapayService;
use App\DataTransferObjects\Pawapay\DepositRequest;
use Illuminate\Support\Str;

$pawapay = app(PawapayService::class);

// Générer un UUID AVANT d'appeler l'API (idempotence)
$depositId = Str::uuid()->toString();

// Créer une transaction locale
$transaction = Transaction::create([
    'transaction_id' => $depositId,
    'user_id' => auth()->id(),
    'type' => TransactionType::DEPOSIT,
    'status' => TransactionStatus::PENDING,
    'amount' => 10000, // montant en centimes (100 XAF)
    'deposit_id' => $depositId,
    'provider' => 'MTN_MOMO_COG',
    'currency' => 'XAF',
]);

// Préparer la requête
$request = new DepositRequest(
    depositId: $depositId,
    phoneNumber: '242064567890', // format: code pays + numéro, pas de +
    provider: 'MTN_MOMO_COG',
    amount: '100', // string, en unités de devise (pas centimes)
    currency: 'XAF',
    clientReferenceId: 'COMMANDE-123',
    customerMessage: 'Paiement location',
    metadata: ['order_id' => '123']
);

// Appeler l'API
$response = $pawapay->initiateDeposit($request);

// Mettre à jour la transaction
$transaction->update([
    'status' => TransactionStatus::tryFrom($response['status']) ?? TransactionStatus::PENDING,
    'raw_response' => $response,
]);

// Vérifier le statut
if ($response['status'] === 'REJECTED') {
    // Gérer le rejet
    $failureReason = $response['failureReason'] ?? [];
    Log::error('Deposit rejected', $failureReason);
}
```

#### Vérifier le statut d'un dépôt

```php
$depositId = '...';
$status = $pawapay->getDepositStatus($depositId);

if ($status['status'] === 'FOUND') {
    $data = $status['data'];
    // $data['status'] peut être: COMPLETED, FAILED, SUBMITTED, etc.
}
```

#### Initier un retrait (payout)

```php
use App\DataTransferObjects\Pawapay\PayoutRequest;

$payoutId = Str::uuid()->toString();

$request = new PayoutRequest(
    payoutId: $payoutId,
    phoneNumber: '242064567890',
    provider: 'MTN_MOMO_COG',
    amount: '50',
    currency: 'XAF'
);

$response = $pawapay->initiatePayout($request);
// Statuts possibles: ACCEPTED, ENQUEUED, REJECTED, DUPLICATE_IGNORED
```

#### Initier un remboursement

```php
use App\DataTransferObjects\Pawapay\RefundRequest;

$refundId = Str::uuid()->toString();
$depositIdToRefund = '...'; // UUID du dépôt à rembourser

$request = new RefundRequest(
    refundId: $refundId,
    depositId: $depositIdToRefund,
    amount: '50' // optionnel, sinon remboursement total
);

$response = $pawapay->initiateRefund($request);
```

#### Page de paiement hébergée

```php
$depositId = Str::uuid()->toString();

$paymentPage = $pawapay->createPaymentPage(
    depositId: $depositId,
    returnUrl: route('tenant.payments'),
    amount: '100',
    currency: 'XAF',
    clientReferenceId: 'COMMANDE-123'
);

// Rediriger vers la page hébergée
return redirect($paymentPage['redirectUrl']);
```

#### Prédire le provider d'un numéro

```php
$result = $pawapay->predictProvider('242064567890');

// $result = [
//     'provider' => 'MTN_MOMO_COG',
//     'phoneNumber' => '242064567890',
//     'country' => 'COG'
// ]
```

---

## Utilisation côté frontend

### Composants Livewire disponibles

#### 1. `InitiateDeposit` - Formulaire de paiement

Utilisé pour initier un paiement depuis l'interface utilisateur.

**Utilisation dans une vue Blade :**

```blade
<livewire:payment.initiate-deposit 
    :amount="10000" 
    :purpose="'visit_pass'" 
    :reference-id="$visitPass->id" 
    :return-url="route('my-visit-passes.show', $visitPass)" 
/>
```

**Props :**

- `amount` (int, required) : Montant en centimes
- `purpose` (string, optional) : Type de paiement (ex: 'visit_pass', 'rent_payment')
- `referenceId` (string, optional) : Référence interne (ID de la ressource associée)
- `returnUrl` (string, optional) : URL de retour après paiement

**Fonctionnalités :**

- Formulaire avec numéro de téléphone, sélection d'opérateur, message optionnel
- Bouton "Payer directement" (initie deposit via API)
- Bouton "Payer via page hébergée" (redirige vers page PawaPay)
- Affichage des erreurs
- États de chargement
- Redirection automatique après succès

#### 2. `TransactionsList` - Liste des transactions

Affiche l'historique des transactions de l'utilisateur.

**Utilisation :**

```blade
<livewire:payment.transactions-list />
```

**Fonctionnalités :**

- Pagination (10 par page)
- Filtre par type (tous, dépôts, retraits, remboursements)
- Badge de statut coloré
- Bouton "Détails" pour chaque transaction
- Polling automatique toutes les 10s si transactions pending

#### 3. `TransactionStatus` - Détails d'une transaction

Affiche les détails complets d'une transaction.

**Utilisation :**

```blade
<livewire:payment.transaction-status :transactionId="$transaction->transaction_id" />
```

**Fonctionnalités :**

- Affichage de tous les détails (montant, statut, provider, dates)
- Badge de statut
- Bouton "Vérifier le statut" (interroge l'API PawaPay)
- Bouton "Réessayer" si échoué
- Polling automatique toutes les 5s si pending
- Section collapsible pour le `raw_response`

### Routes frontend

```php
// Routes dans le groupe tenant
Route::get('/transactions', [TransactionController::class, 'index'])
    ->name('tenant.transactions.index');

Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
    ->name('tenant.transactions.show');
```

---

## Webhooks & Callbacks

### Endpoint callback

**Route :** `POST /webhooks/pawapay/callback`
**Controller :** `PawapayCallbackController@handle`
**Middleware :** Aucun (`withoutMiddleware(['web', 'csrf'])`) — les webhooks ne peuvent pas envoyer de token CSRF

### Traitement du callback

Le `PawapayCallbackController` :

1. Reçoit le payload JSON
2. Optionnellement vérifie la signature (si `PAWAPAY_CALLBACK_VERIFY_SIGNATURE=true`)
3. Appelle `PawapayService::handleCallback()` qui met à jour la transaction
4. Répond immédiatement `200 OK` pour acquitter

### Idempotence

Le même callback peut arriver plusieurs fois. Le service vérifie si le statut a changé avant de mettre à jour :

```php
if ($transaction->status !== $status->value) {
    $transaction->update([
        'status' => $status->value,
        'raw_response' => array_merge($transaction->raw_response ?? [], $payload),
    ]);
}
```

### IPs à whitelister

Si votre serveur a un firewall, whitelister les IPs de PawaPay :

- **Sandbox** : `52.214.140.196`, `99.80.130.155`, `18.200.110.148`
- **Production** : Voir https://docs.pawapay.io/v2/docs/welcome

---

## Tests

### Tests Feature

Les tests sont dans `tests/Feature/Payment/PawapayIntegrationTest.php`.

**Exécution :**

```bash
php artisan test --filter=PawapayIntegrationTest
```

**Ce qui est testé :**

- Initiation de dépôt (avec mock HTTP)
- Vérification du statut
- Initiation de payout
- Initiation de refund
- Prédiction de provider
- Traitement des callbacks
- Normalisation des numéros de téléphone
- Scopes du modèle Transaction
- Attributs calculés du modèle

### Tests Livewire

```bash
php artisan test --filter=TransactionsListTest
php artisan test --filter=TransactionStatusTest
```

---

## Sandbox & Production

### Mode Sandbox

Le compte sandbox est **immédiatement accessible** après inscription sur https://www.pawapay.io/plans.

**Caractéristiques :**

- Aucun argent réel
- Accès à tous les providers
- Numéros de test pour simuler succès/échec
- Réponses plus rapides qu'en production

**Numéros de test** (exemple pour Congo-Brazzaville, MTN) :

| Numéro | Résultat |
|--------|----------|
| 242064000001 | `COMPLETED` |
| 242064000002 | `FAILED` (PAYER_NOT_FOUND) |
| 242064000003 | `FAILED` (INSUFFICIENT_BALANCE) |
| 242064000004 | `FAILED` (PAYMENT_NOT_APPROVED) |

Voir la liste complète dans `.agents/skills/pawapay/references/providers-and-test-numbers.md` ou https://docs.pawapay.io/v2/docs/test_numbers

### Passage en Production

**Checklist :**

1. ✅ Compléter le KYC/onboarding sur le dashboard sandbox
2. ✅ Générer un token de production distinct (ne **jamais** réutiliser le token sandbox)
3. ✅ Mettre à jour `.env` :
   ```env
   PAWAPAY_ENV=production
   PAWAPAY_API_TOKEN=votre_token_production
   ```
4. ✅ Configurer les callbacks dans le dashboard production
5. ✅ Activer la vérification de signature (`PAWAPAY_CALLBACK_VERIFY_SIGNATURE=true`)
6. ✅ Tester avec de petits montants réels
7. ✅ Monitorer les logs et statuts

**URLs production :**

- API : `https://api.pawapay.io`
- Dashboard : `https://dashboard.pawapay.io`
- Status page : https://status.pawapay.cloud/

---

## Dépannage

### Le dépôt reste `PENDING` / `SUBMITTED`

- Le client doit valider le paiement sur son téléphone (saisir PIN)
- Vérifier que le numéro est correct et enregistré chez l'opérateur
- Consulter le status en temps réel : https://status.pawapay.cloud/

### `REJECTED` avec `INVALID_PHONE_NUMBER`

- Le numéro doit être au format : code pays + numéro, **sans +, sans espaces, sans zéro initial**
- Exemple correct : `242064567890` (Congo-Brazzaville)
- Exemple incorrect : `+242 064 567 890` ou `064567890`
- Utiliser `PawapayService::normalizePhoneNumber()` pour nettoyer
- Utiliser `POST /v2/toolkit/predict-provider` pour valider

### `REJECTED` avec `AMOUNT_OUT_OF_BOUNDS`

- Chaque provider a des montants min/max
- Vérifier via `GET /v2/active-conf` ou le dashboard
- Exemple : MTN Congo accepte généralement 500 XAF - 500 000 XAF

### Le callback n'arrive jamais

1. Vérifier que l'URL du callback est configurée dans le dashboard PawaPay
2. Vérifier que l'URL est **publiquement accessible** (pas localhost, pas IP privée)
3. Vérifier les logs du serveur web (erreurs 500, timeout)
4. Utiliser `POST /v2/deposits/{depositId}/resend-callback` pour renvoyer manuellement

### `PROVIDER_TEMPORARILY_UNAVAILABLE`

- L'opérateur est temporairement hors service
- Vérifier https://status.pawapay.cloud/
- Réessayer plus tard ou proposer un autre provider

### Erreur "Invalid signature"

- Vérifier que `PAWAPAY_CALLBACK_VERIFY_SIGNATURE` est bien `false` en dev
- En production, échanger les clés publiques via le dashboard
- Consulter https://docs.pawapay.io/v2/docs/signatures

---

## Ressources

- **Documentation officielle** : https://docs.pawapay.io/v2/docs/welcome
- **Référence API (OpenAPI)** : https://docs.pawapay.io/v2/api-reference
- **Guide "Going live"** : https://docs.pawapay.io/v2/docs/going_live
- **Status de la plateforme** : https://status.pawapay.cloud/
- **Postman collection** : https://docs.pawapay.io/v2/docs/postman
- **Support** : support@pawapay.io

---

## Changelog

### 2026-08-24
- ✅ Intégration complète PawaPay API v2
- ✅ Service `PawapayService` avec toutes les méthodes (deposits, payouts, refunds, payment page, toolkit)
- ✅ Modèle `Transaction` avec enums, scopes, attributs calculés
- ✅ DTOs typés (`DepositRequest`, `PayoutRequest`, `RefundRequest`)
- ✅ Exception `PawaPayException` pour gestion d'erreurs
- ✅ Composants Livewire : `InitiateDeposit`, `TransactionsList`, `TransactionStatus`
- ✅ Controller `PawapayCallbackController` pour webhooks
- ✅ Routes tenant pour transactions
- ✅ Vues Blade pour interface utilisateur
- ✅ Tests Feature complets
- ✅ Configuration complète (`.env.example`, `config/pawapay.php`)
- ✅ Documentation exhaustive
