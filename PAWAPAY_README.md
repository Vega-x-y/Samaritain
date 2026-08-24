# PawaPay Integration - Quick Start

🚀 **Intégration complète de PawaPay (Mobile Money) pour l'application Samaritain.**

## 🎯 Ce qui a été fait

✅ **Backend complet**
- Service `PawapayService` avec toutes les méthodes API v2
- Modèle `Transaction` avec enums, scopes, et relations
- DTOs typés pour Deposits, Payouts, Refunds
- Exception `PawaPayException` pour gestion d'erreurs
- Controller de callbacks sécurisé
- Routes tenant pour l'historique des transactions

✅ **Frontend complet (Livewire + Flux UI)**
- Composant `InitiateDeposit` - formulaire de paiement
- Composant `TransactionsList` - historique avec pagination et filtres
- Composant `TransactionStatus` - détails d'une transaction avec polling temps réel
- Pages Blade pour tenant

✅ **Tests**
- Tests Feature complets (9 tests)
- Tests Livewire (2 composants)
- Mocks HTTP pour simulation

✅ **Documentation**
- `PAWAPAY_INTEGRATION.md` - Documentation complète
- `PAWAPAY_EXAMPLES.md` - Exemples d'utilisation pratiques
- Ce fichier README - Quick start

---

## ⚡ Quick Start (5 minutes)

### 1. Configuration

```bash
# Copier les variables d'environnement
cp .env.example .env
```

Compléter dans `.env` :

```env
PAWAPAY_ENV=sandbox
PAWAPAY_API_TOKEN=votre_token_sandbox_ici
PAWAPAY_CURRENCY=XAF
PAWAPAY_COUNTRY=COG
```

### 2. Obtenir un token sandbox

1. Créer un compte sur https://www.pawapay.io/plans (gratuit, instantané)
2. Dashboard sandbox : https://dashboard.sandbox.pawapay.io
3. Aller dans **System Configuration → API Tokens**
4. Générer un token et le copier dans `.env`

### 3. Configurer le callback

Dans le dashboard PawaPay :

1. **System Configuration → Callback URLs**
2. URL : `https://votre-domaine.com/webhooks/pawapay/callback`
3. Activer pour Deposits, Payouts, Refunds

> ⚠️ En dev local, utiliser ngrok ou expose pour avoir une URL publique :
> ```bash
> ngrok http 8000
> # Puis utiliser l'URL ngrok comme callback
> ```

### 4. Tester en sandbox

**Numéros de test Congo-Brazzaville (MTN)** :

| Numéro | Résultat |
|--------|----------|
| 242064000001 | ✅ `COMPLETED` |
| 242064000002 | ❌ `FAILED` (PAYER_NOT_FOUND) |
| 242064000003 | ❌ `FAILED` (INSUFFICIENT_BALANCE) |

Voir `.agents/skills/pawapaw/references/providers-and-test-numbers.md` pour la liste complète.

---

## 🚀 Utilisation

### Dans un controller

```php
use App\Services\PawapayService;
use App\DataTransferObjects\Pawapay\DepositRequest;

$pawapay = app(PawapayService::class);

$response = $pawapay->initiateDeposit(new DepositRequest(
    depositId: Str::uuid()->toString(),
    phoneNumber: '242064000001',  // numéro de test
    provider: 'MTN_MOMO_COG',
    amount: '100',
    currency: 'XAF'
));

// $response['status'] peut être: ACCEPTED, REJECTED, DUPLICATE_IGNORED
```

### Dans une vue Blade

```blade
<livewire:payment.initiate-deposit
    :amount="10000"
    :purpose="'order'"
    :reference-id="$order->id"
/>
```

---

## 📁 Fichiers importants

### Backend

- `app/Services/PawapayService.php` - Service principal
- `app/Models/Transaction.php` - Modèle
- `app/Http/Controllers/PawapayCallbackController.php` - Callbacks
- `app/Enums/TransactionStatus.php` - Statuts
- `config/pawapay.php` - Configuration

### Frontend

- `app/Livewire/Payment/InitiateDeposit.php` - Formulaire de paiement
- `app/Livewire/Payment/TransactionsList.php` - Liste des transactions
- `app/Livewire/Payment/TransactionStatus.php` - Détails transaction
- `resources/views/livewire/payment/` - Vues Blade
- `resources/views/pages/tenant/transactions/` - Pages

### Routes

```php
// Tenant
Route::get('/transactions', [TransactionController::class, 'index'])->name('tenant.transactions.index');
Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('tenant.transactions.show');

// Webhook (public)
Route::post('/webhooks/pawapay/callback', [PawapayCallbackController::class, 'handle'])->name('pawapay.callback');
```

---

## 🧪 Tests

```bash
# Tests complets
php artisan test --filter=PawapayIntegrationTest

# Tests Livewire
php artisan test --filter=TransactionsListTest
php artisan test --filter=TransactionStatusTest
```

---

## 📚 Documentation

1. **PAWAPAY_INTEGRATION.md** - Documentation complète (architecture, API, troubleshooting)
2. **PAWAPAY_EXAMPLES.md** - Exemples pratiques (commandes, loyers, refunds, bulk payouts)
3. **PawaPay officiel** :
   - Docs : https://docs.pawapay.io/v2/docs/welcome
   - API Reference : https://docs.pawapay.io/v2/api-reference
   - Status : https://status.pawapay.cloud/

---

## ⚠️ Points importants

### Asynchrone

PawaPay est **asynchrone**. Ne jamais considérer un paiement réussi avant le callback `COMPLETED`.

### Idempotence

Toujours générer et persister le `depositId`/`payoutId`/`refundId` **AVANT** d'appeler l'API.

```php
// ✅ BON
$depositId = Str::uuid()->toString();
Transaction::create(['transaction_id' => $depositId, ...]);
$pawapay->initiateDeposit(new DepositRequest(depositId: $depositId, ...));

// ❌ MAUVAIS (risque de doublon si retry)
$response = $pawapay->initiateDeposit(...);
Transaction::create(['transaction_id' => $response['depositId'], ...]);
```

### Montants

- En **base de données** : centimes (`10000` = 100 XAF)
- Dans **l'API PawaPay** : unités de devise (string `"100"`)
- Dans **l'affichage** : diviser par 100

```php
// BDD → API
$apiAmount = (string) ($transaction->amount / 100);

// BDD → Affichage
{{ number_format($transaction->amount / 100, 0) }} XAF
```

### Numéros de téléphone

Format PawaPay : **code pays + numéro, sans +, sans espaces, sans zéro initial**

```php
// ✅ BON
'242064567890'

// ❌ MAUVAIS
'+242 064 567 890'  // contient + et espaces
'064567890'          // manque code pays
```

Utiliser `$pawapay->normalizePhoneNumber()` pour nettoyer.

---

## 🛠️ Sandbox → Production

**Checklist** :

1. ☑️ Compléter le KYC sur le dashboard sandbox
2. ☑️ Générer un token **production** distinct
3. ☑️ Mettre à jour `.env` :
   ```env
   PAWAPAY_ENV=production
   PAWAPAY_API_TOKEN=votre_token_production
   ```
4. ☑️ Configurer les callbacks dans le dashboard production
5. ☑️ Activer la vérification de signature
6. ☑️ Tester avec de petits montants réels

---

## 🤝 Support

- **PawaPay** : support@pawapay.io
- **Documentation** : `PAWAPAY_INTEGRATION.md`
- **Exemples** : `PAWAPAY_EXAMPLES.md`
- **Skill agent** : `.agents/skills/pawapay/SKILL.md`

---

## 📊 Statuts possibles

| Statut | Description | Final ? |
|--------|-------------|---------|
| `PENDING` | En attente | Non |
| `SUBMITTED` | Soumis au provider | Non |
| `ACCEPTED` | Accepté par PawaPay | Non |
| `COMPLETED` | ✅ Réussi | Oui |
| `FAILED` | ❌ Échoué | Oui |
| `REJECTED` | ❌ Rejeté à l'initiation | Oui |
| `ENQUEUED` | En file (payout) | Non |
| `CANCELLED` | Annulé | Oui |
| `DUPLICATE_IGNORED` | Doublon ignoré | Oui |

---

## 🎉 C'est prêt !

L'intégration est **complète et prête à l'emploi**. Consulter `PAWAPAY_INTEGRATION.md` pour plus de détails.
