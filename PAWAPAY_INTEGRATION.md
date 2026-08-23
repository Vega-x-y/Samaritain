# Intégration PawaPay

Ce document décrit l'intégration complète de PawaPay pour les paiements Mobile Money dans l'application Samaritain.

## Vue d'ensemble

PawaPay est une passerelle de paiement Mobile Money qui permet de:
- **Collecter des paiements** (deposits) - les clients vous payent
- **Envoyer de l'argent** (payouts) - vous payez vos clients
- **Rembourser** (refunds) - rembourser un dépôt complété

## Architecture

### Composants principaux

1. **Service** - `app/Services/PawapayService.php`
   - Gère toutes les interactions avec l'API PawaPay
   - Méthodes pour deposits, payouts, refunds
   - Gestion des callbacks
   - Normalisation des numéros de téléphone

2. **DTOs** - `app/DataTransferObjects/Pawapay/*`
   - `DepositRequest` - Pour initier un dépôt
   - `PayoutRequest` - Pour initier un retrait
   - `RefundRequest` - Pour initier un remboursement

3. **Enums**
   - `TransactionStatus` - Statuts des transactions (PENDING, COMPLETED, FAILED, etc.)
   - `TransactionType` - Types de transactions (DEPOSIT, PAYOUT, REFUND)

4. **Modèle** - `app/Models/Transaction`
   - Stocke toutes les transactions PawaPay
   - Relations avec User, VisitPass, RentPayment

5. **Controller** - `app/Http/Controllers/PawapayCallbackController`
   - Reçoit les callbacks de PawaPay
   - Met à jour automatiquement les transactions

6. **Configuration** - `config/pawapay.php`
   - Toute la configuration centralisée

## Configuration

### Variables d'environnement

Ajoutez ces variables à votre fichier `.env`:

```env
# PawaPay Configuration
PAWAPAY_BASE_URL=https://api.sandbox.pawapay.io
PAWAPAY_TOKEN=your-api-token-here
PAWAPAY_DEFAULT_CURRENCY=CDF
PAWAPAY_CALLBACK_URL=https://votre-site.com/webhooks/pawapay/callback
PAWAPAY_RETURN_URL=https://votre-site.com/payments/return
PAWAPAY_VERIFY_CALLBACK_SIGNATURE=false
PAWAPAY_TIMEOUT=30
PAWAPAY_RETRY_TIMES=2
```

### Providers disponibles

Configurez les providers dans `config/pawapay.php`:

```php
'providers' => [
    'MTN_MOMO_COG' => 'MTN Mobile Money',
    'AIRTEL_COG' => 'Airtel Money',
    'MTN_MOMO_COD' => 'MTN Mobile Money (RDC)',
    'AIRTEL_COD' => 'Airtel Money (RDC)',
    'ORANGE_COD' => 'Orange Money (RDC)',
    'VODACOM_COD' => 'M-Pesa (RDC)',
],
```

### Configuration des callbacks

Dans le dashboard PawaPay:
1. Allez dans **System Configuration → Callback URLs**
2. Ajoutez votre URL de callback: `https://votre-site.com/webhooks/pawapay/callback`
3. Testez la connexion

## Utilisation

### 1. Initier un dépôt (collecter un paiement)

```php
use App\DataTransferObjects\Pawapay\DepositRequest;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Services\PawapayService;
use Illuminate\Support\Str;

$pawapay = app(PawapayService::class);

// 1. Générer et persister le depositId AVANT l'appel API
$depositId = Str::uuid()->toString();

$transaction = Transaction::create([
    'transaction_id' => Str::uuid()->toString(),
    'user_id' => auth()->id(),
    'deposit_id' => $depositId,
    'type' => TransactionType::DEPOSIT,
    'status' => TransactionStatus::PENDING,
    'amount' => 1500, // En centimes (15 CDF)
    'currency' => 'CDF',
    'provider' => 'MTN_MOMO_COG',
]);

// 2. Initier le dépôt via PawaPay
$request = new DepositRequest(
    depositId: $depositId,
    phoneNumber: '242064567890', // Numéros uniquement, pas de +
    provider: 'MTN_MOMO_COG',
    amount: '15', // String, pas de zéros de tête
    currency: 'CDF',
    clientReferenceId: 'INV-123456',
    customerMessage: 'Paiement loyer', // 4-22 caractères alphanumériques
);

try {
    $response = $pawapay->initiateDeposit($request);
    
    // Toujours vérifier le status dans la réponse
    if ($response['status'] === 'ACCEPTED') {
        // Dépôt accepté, attendre le callback pour le statut final
        $transaction->update(['status' => TransactionStatus::ACCEPTED]);
    } elseif ($response['status'] === 'REJECTED') {
        // Dépôt rejeté immédiatement
        $transaction->update([
            'status' => TransactionStatus::REJECTED,
            'raw_response' => $response,
        ]);
        
        $failureReason = $response['failureReason']['failureCode'] ?? 'Unknown';
        // Afficher l'erreur à l'utilisateur
    }
} catch (PawaPayException $e) {
    // Erreur réseau ou configuration
    // NE PAS marquer la transaction comme FAILED
    // Le statut reste PENDING jusqu'à confirmation
}
```

### 2. Page de paiement hébergée

Si vous ne voulez pas gérer le formulaire vous-même:

```php
$depositId = Str::uuid()->toString();

// Créer la transaction en PENDING
$transaction = Transaction::create([
    'transaction_id' => Str::uuid()->toString(),
    'user_id' => auth()->id(),
    'deposit_id' => $depositId,
    'type' => TransactionType::DEPOSIT,
    'status' => TransactionStatus::PENDING,
    'amount' => 1500,
    'currency' => 'CDF',
]);

// Créer la page de paiement
$response = $pawapay->createPaymentPage(
    depositId: $depositId,
    returnUrl: route('payments.return'),
    amount: '15',
    currency: 'CDF',
    clientReferenceId: 'INV-123456'
);

// Rediriger l'utilisateur vers la page PawaPay
return redirect($response['redirectUrl']);
```

**Important**: Toujours vérifier le statut final via callback ou API, jamais uniquement sur le retour de redirection.

### 3. Initier un payout (envoyer de l'argent)

```php
use App\DataTransferObjects\Pawapay\PayoutRequest;

$payoutId = Str::uuid()->toString();

$transaction = Transaction::create([
    'transaction_id' => Str::uuid()->toString(),
    'user_id' => $recipientUserId,
    'payout_id' => $payoutId,
    'type' => TransactionType::PAYOUT,
    'status' => TransactionStatus::PENDING,
    'amount' => 5000, // 50 CDF
    'currency' => 'CDF',
    'provider' => 'MTN_MOMO_COG',
]);

$request = new PayoutRequest(
    payoutId: $payoutId,
    phoneNumber: '242064567890',
    provider: 'MTN_MOMO_COG',
    amount: '50',
    currency: 'CDF',
);

$response = $pawapay->initiatePayout($request);

// Gérer ACCEPTED, ENQUEUED, ou REJECTED
if ($response['status'] === 'ENQUEUED') {
    // Payout en file d'attente, sera traité plus tard
    $transaction->update(['status' => TransactionStatus::ENQUEUED]);
}
```

### 3b. Payouts groupés (bulk)

Pour envoyer de l'argent à plusieurs personnes en une seule requête :

```php
$payouts = [
    new PayoutRequest(
        payoutId: Str::uuid()->toString(),
        phoneNumber: '242064567890',
        provider: 'MTN_MOMO_COG',
        amount: '50',
        currency: 'CDF',
    ),
    new PayoutRequest(
        payoutId: Str::uuid()->toString(),
        phoneNumber: '242064567891',
        provider: 'AIRTEL_COG',
        amount: '30',
        currency: 'CDF',
    ),
    // ... jusqu'à 1000 payouts par requête
];

$responses = $pawapay->initiateBulkPayout($payouts);

// $responses est un tableau avec le statut de chaque payout
foreach ($responses as $index => $response) {
    if ($response['status'] === 'ACCEPTED') {
        // Payout accepté
    } elseif ($response['status'] === 'REJECTED') {
        // Payout rejeté, voir failureReason
    }
}
```

### 4. Rembourser un dépôt

```php
use App\DataTransferObjects\Pawapay\RefundRequest;

// Le dépôt doit être COMPLETED
$originalDeposit = Transaction::where('deposit_id', $originalDepositId)
    ->where('status', TransactionStatus::COMPLETED)
    ->firstOrFail();

$refundId = Str::uuid()->toString();

$transaction = Transaction::create([
    'transaction_id' => Str::uuid()->toString(),
    'user_id' => $originalDeposit->user_id,
    'refund_id' => $refundId,
    'type' => TransactionType::REFUND,
    'status' => TransactionStatus::PENDING,
    'amount' => $originalDeposit->amount, // Montant à rembourser
    'currency' => $originalDeposit->currency,
]);

$request = new RefundRequest(
    refundId: $refundId,
    depositId: $originalDepositId,
    amount: '15', // Optionnel, si null = remboursement total
);

$response = $pawapay->initiateRefund($request);
```

### 5. Vérifier le statut d'une transaction

```php
// Pour un dépôt
$status = $pawapay->getDepositStatus($depositId);

if ($status['status'] === 'FOUND') {
    $data = $status['data'];
    // $data['status'] peut être: SUBMITTED, COMPLETED, FAILED
}

// Pour un payout
$status = $pawapay->getPayoutStatus($payoutId);

// Pour un refund
$status = $pawapay->getRefundStatus($refundId);
```

### 6. Annuler un payout en file

```php
// Seulement si le payout est ENQUEUED
$response = $pawapay->cancelPayout($payoutId);

if ($response['status'] === 'CANCELLED') {
    $transaction->update(['status' => TransactionStatus::CANCELLED]);
}
```

### 7. Payouts groupés (bulk)

Pour envoyer de l'argent à plusieurs destinataires en une seule requête (jusqu'à 1000 payouts) :

```php
$payouts = [
    new PayoutRequest(
        payoutId: Str::uuid()->toString(),
        phoneNumber: '242064567890',
        provider: 'MTN_MOMO_COG',
        amount: '50',
        currency: 'CDF',
    ),
    new PayoutRequest(
        payoutId: Str::uuid()->toString(),
        phoneNumber: '242064567891',
        provider: 'AIRTEL_COG',
        amount: '30',
        currency: 'CDF',
    ),
];

$responses = $pawapay->initiateBulkPayout($payouts);

// Chaque response a son propre statut (ACCEPTED/ENQUEUED/REJECTED)
foreach ($responses as $index => $response) {
    $payoutId = $response['payoutId'] ?? null;
    $status = $response['status'] ?? null;
    
    // Mettre à jour la transaction correspondante
}
```

**Avantages du bulk payout :**
- ✅ Une seule requête HTTP au lieu de N requêtes
- ✅ Plus rapide et plus efficace
- ✅ Idéal pour salaires, remboursements multiples, etc.

## Callbacks

Les callbacks sont automatiquement gérés par `PawapayCallbackController`.

### Workflow des callbacks

1. PawaPay envoie un POST à `/webhooks/pawapay/callback`
2. Le controller appelle `PawapayService::handleCallback()`
3. La transaction est automatiquement mise à jour
4. Le controller répond 200 OK immédiatement

### Idempotence

Le même callback peut arriver plusieurs fois. Le service gère automatiquement la déduplication:
- Si le statut n'a pas changé, aucune mise à jour
- Les raw_response sont fusionnées

### Tester les callbacks manuellement

Envoyez un POST à votre endpoint avec ce payload:

```bash
curl -X POST https://votre-site.com/webhooks/pawapay/callback \
  -H "Content-Type: application/json" \
  -d '{
    "depositId": "votre-deposit-id",
    "status": "COMPLETED",
    "requestedAmount": "15",
    "depositedAmount": "15",
    "currency": "CDF",
    "country": "COG",
    "correspondent": "MTN_MOMO_COG"
  }'
```

## Helpers et utilitaires

### Prédire le provider

```php
$result = $pawapay->predictProvider('242064567890');

// {
//   'provider' => 'MTN_MOMO_COG',
//   'phoneNumber' => '242064567890',
//   'country' => 'COG'
// }
```

### Normaliser un numéro de téléphone

```php
$normalized = $pawapay->normalizePhoneNumber('+242 06 456 7890');
// '242064567890'
```

### Obtenir les providers configurés

```php
$providers = $pawapay->availableProviders();
// ['MTN_MOMO_COG' => 'MTN Mobile Money', ...]
```

### Configuration active (source de vérité)

```php
$config = $pawapay->getActiveConfiguration();

// Retourne les providers réellement configurés sur votre compte PawaPay
// avec leurs limites de transaction, devises supportées, etc.
```

### Disponibilité en temps réel

```php
$availability = $pawapay->getAvailability();

// Vérifier si un provider est down ou en maintenance
```

## Statuts des transactions

### Lifecycle d'un dépôt

1. `PENDING` - Transaction créée localement
2. `ACCEPTED` - PawaPay a accepté la demande
3. `SUBMITTED` - Envoyé au provider
4. Client entre son PIN sur son téléphone
5. `COMPLETED` - Paiement réussi ✅
   OU
   `FAILED` - Paiement échoué ❌

### Statuts finaux

- `COMPLETED` - Succès
- `FAILED` - Échec
- `CANCELLED` - Annulé (payouts seulement)
- `REJECTED` - Rejeté à l'initiation

### Statuts temporaires

- `PENDING` - En attente locale
- `SUBMITTED` - Soumis au provider
- `ACCEPTED` - Accepté par PawaPay
- `ENQUEUED` - En file (payouts)
- `DUPLICATE_IGNORED` - Doublon

## Tests

### Lancer les tests

```bash
php artisan test --filter=Pawapay --compact
```

### Tests disponibles

- `PawapayServiceTest` - Tests unitaires du service
- `PawapayCallbackControllerTest` - Tests du controller de callbacks

### Mode sandbox

Utilisez les numéros de test fournis par PawaPay selon le pays/opérateur.

Exemples pour Congo-Brazzaville (COG):
- **Succès**: `242064000001` à `242064000099`
- **Échec (insufficient balance)**: `242064000100`
- **Échec (invalid account)**: `242064000101`

Voir la documentation PawaPay pour la liste complète par pays.

## Sécurité

### Tokens API

- ❌ Jamais exposer le token côté client
- ✅ Toujours en variable d'environnement
- ✅ Token sandbox ≠ token production

### Signatures (RFC-9421)

Pour la production, activez la vérification des signatures:

```env
PAWAPAY_VERIFY_CALLBACK_SIGNATURE=true
```

Configurez vos clés publiques dans le dashboard PawaPay.

### CSRF

Les routes de callback excluent automatiquement la protection CSRF (webhooks externes).

## Débogage

### Logs

Tous les appels API et callbacks sont loggés:

```bash
php artisan pail --filter=PawaPay
```

### Rechercher une transaction

```php
// Par depositId
$transaction = Transaction::where('deposit_id', $depositId)->first();

// Par payoutId
$transaction = Transaction::where('payout_id', $payoutId)->first();

// Par refundId
$transaction = Transaction::where('refund_id', $refundId)->first();

// Transactions en attente
$pending = Transaction::pending()->get();

// Transactions complétées
$completed = Transaction::completed()->get();
```

### Renvoyer un callback manqué

```php
// Si vous avez manqué un callback, demandez à PawaPay de le renvoyer
$pawapay->resendDepositCallback($depositId);
$pawapay->resendPayoutCallback($payoutId);
$pawapay->resendRefundCallback($refundId);
```

## Limites et contraintes

### Montants

- Format: string, pas de zéros de tête (sauf < 1)
- Exemples valides: `"15"`, `"0.5"`, `"1000.99"`
- Exemples invalides: `"015"`, `".5"`, `"1,000"`

### Numéros de téléphone

- Format: chiffres uniquement, pas de `+` ni espaces
- Code pays obligatoire
- Pas de zéro initial après le code pays
- Exemple: `242064567890` (pas `+242 06 456 7890`)

### Messages client

- 4 à 22 caractères
- Alphanumériques seulement (pas d'accents ni symboles)
- Visible sur le reçu SMS du client (selon opérateur)

### Metadata

- Max 10 champs par transaction
- Utilisés pour recherche dans le dashboard
- Non visibles par le client

## Checklist de production

- [ ] Token de production généré et configuré
- [ ] URL de callback configurée dans le dashboard
- [ ] Callbacks testés en sandbox
- [ ] Numéros de test validés pour chaque provider
- [ ] Gestion des erreurs implémentée
- [ ] Logs et monitoring en place
- [ ] Signatures activées (optionnel mais recommandé)
- [ ] Tests passent tous
- [ ] Documentation lue par l'équipe

## Ressources

- Documentation officielle: https://docs.pawapay.io/v2/docs/welcome
- API Reference: https://docs.pawapay.io/v2/api-reference
- Dashboard sandbox: https://dashboard.sandbox.pawapay.io
- Statut de la plateforme: https://status.pawapay.cloud/
- Support: support@pawapay.io
