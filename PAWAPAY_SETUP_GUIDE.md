# Guide de configuration PawaPay

Guide rapide pour mettre en place l'intégration PawaPay de zéro.

## 1. Prérequis

- [ ] Compte PawaPay créé sur https://www.pawapay.io/plans
- [ ] Accès au dashboard sandbox: https://dashboard.sandbox.pawapay.io
- [ ] Token API généré (Dashboard → System Configuration → API Tokens)

## 2. Installation (déjà fait)

L'intégration PawaPay est déjà installée dans ce projet. Voici ce qui a été créé:

### Fichiers de configuration
- ✅ `config/pawapay.php` - Configuration centralisée
- ✅ `.env.pawapay.example` - Variables d'environnement exemple

### Services et DTOs
- ✅ `app/Services/PawapayService.php` - Service principal
- ✅ `app/DataTransferObjects/Pawapay/*` - DTOs pour requêtes
- ✅ `app/Enums/TransactionStatus.php` - Enum des statuts
- ✅ `app/Enums/TransactionType.php` - Enum des types
- ✅ `app/Exceptions/PawaPayException.php` - Exception personnalisée

### Contrôleurs et routes
- ✅ `app/Http/Controllers/PawapayCallbackController.php`
- ✅ Route `/webhooks/pawapay/callback` configurée

### Modèle et migration
- ✅ `app/Models/Transaction.php` - Modèle amélioré
- ✅ Migration pour `refund_id`

### Tests
- ✅ `tests/Feature/PawapayServiceTest.php`
- ✅ `tests/Feature/PawapayCallbackControllerTest.php`

### Documentation
- ✅ `PAWAPAY_INTEGRATION.md` - Documentation complète
- ✅ `app/Examples/PawapayUsageExample.php` - Exemples de code

## 3. Configuration de l'environnement

### Étape 3.1: Copier les variables

Copiez le contenu de `.env.pawapay.example` dans votre `.env`:

```bash
cat .env.pawapay.example >> .env
```

Ou ajoutez manuellement ces lignes à votre `.env`:

```env
PAWAPAY_BASE_URL=https://api.sandbox.pawapay.io
PAWAPAY_TOKEN=votre-token-sandbox-ici
PAWAPAY_DEFAULT_CURRENCY=CDF
PAWAPAY_CALLBACK_URL=${APP_URL}/webhooks/pawapay/callback
PAWAPAY_RETURN_URL=${APP_URL}/payments/return
PAWAPAY_VERIFY_CALLBACK_SIGNATURE=false
PAWAPAY_TIMEOUT=30
PAWAPAY_RETRY_TIMES=2
```

### Étape 3.2: Remplir le token

1. Allez sur https://dashboard.sandbox.pawapay.io
2. Cliquez sur **System Configuration** → **API Tokens**
3. Créez un nouveau token (ou copiez l'existant)
4. Collez-le dans votre `.env`:

```env
PAWAPAY_TOKEN=sk_sandbox_votre_token_ici_xxxxxxxxxxxx
```

### Étape 3.3: Configurer les providers

Dans `config/pawapay.php`, ajustez la liste des providers selon votre pays:

```php
'providers' => [
    // République du Congo (Congo-Brazzaville)
    'MTN_MOMO_COG' => 'MTN Mobile Money',
    'AIRTEL_COG' => 'Airtel Money',
    
    // République Démocratique du Congo (RDC)
    'MTN_MOMO_COD' => 'MTN Mobile Money',
    'AIRTEL_COD' => 'Airtel Money',
    'ORANGE_COD' => 'Orange Money',
    'VODACOM_COD' => 'M-Pesa',
    
    // Autres pays...
],
```

## 4. Migrations de base de données

Lancez les migrations pour ajouter la colonne `refund_id`:

```bash
php artisan migrate
```

Cela ajoutera:
- ✅ Colonne `refund_id` dans la table `transactions`

## 5. Configuration du callback webhook

### Étape 5.1: Exposer votre serveur local (en développement)

Pour tester les callbacks localement, utilisez ngrok ou un tunnel similaire:

```bash
ngrok http 8000
```

Vous obtiendrez une URL comme: `https://abc123.ngrok.io`

### Étape 5.2: Configurer l'URL dans PawaPay

1. Allez sur https://dashboard.sandbox.pawapay.io
2. **System Configuration** → **Callback URLs**
3. Ajoutez votre URL de callback:
   - Local (dev): `https://abc123.ngrok.io/webhooks/pawapay/callback`
   - Production: `https://votre-domaine.com/webhooks/pawapay/callback`
4. Testez la connexion avec le bouton "Test"

## 6. Tests

### Étape 6.1: Lancer les tests unitaires

```bash
php artisan test --filter=Pawapay --compact
```

Tous les tests devraient passer ✅

### Étape 6.2: Tester avec des numéros sandbox

PawaPay fournit des numéros de test par pays. Exemples pour Congo-Brazzaville:

- **Succès**: `242064000001` à `242064000099`
- **Échec (solde insuffisant)**: `242064000100`
- **Échec (compte invalide)**: `242064000101`

Voir la documentation complète dans `.agents/skills/pawapay/references/providers-and-test-numbers.md`

### Étape 6.3: Test manuel d'un dépôt

```bash
php artisan tinker
```

```php
use App\DataTransferObjects\Pawapay\DepositRequest;
use App\Services\PawapayService;
use App\Models\Transaction;
use App\Enums\TransactionType;
use App\Enums\TransactionStatus;

$pawapay = app(PawapayService::class);

// Créer une transaction
$depositId = \Str::uuid()->toString();
$transaction = Transaction::create([
    'transaction_id' => \Str::uuid()->toString(),
    'user_id' => 1, // Remplacer par un vrai user_id
    'deposit_id' => $depositId,
    'type' => TransactionType::DEPOSIT,
    'status' => TransactionStatus::PENDING,
    'amount' => 1500, // 15 CDF
    'currency' => 'CDF',
]);

// Initier le dépôt
$request = new DepositRequest(
    depositId: $depositId,
    phoneNumber: '242064000001', // Numéro de test succès
    provider: 'MTN_MOMO_COG',
    amount: '15',
    currency: 'CDF',
);

$response = $pawapay->initiateDeposit($request);
dump($response);

// Vérifier le statut
$transaction->refresh();
dump($transaction->status);
```

## 7. Intégration dans votre application

### Exemple: Paiement d'un loyer

```php
use App\Examples\PawapayUsageExample;

$example = app(PawapayUsageExample::class);

// Collecter le paiement
$transaction = $example->collectPayment(
    userId: auth()->id(),
    amount: 5000, // 50 CDF (en centimes)
    phoneNumber: '242064567890',
);

// Rediriger vers une page de confirmation
return redirect()->route('payments.show', $transaction);
```

### Exemple: Page de paiement hébergée

```php
$redirectUrl = $example->createHostedPaymentPage(
    userId: auth()->id(),
    amount: 5000, // 50 CDF
);

// Rediriger l'utilisateur vers PawaPay
return redirect($redirectUrl);
```

## 8. Checklist avant la production

- [ ] Token de **production** généré (différent du sandbox!)
- [ ] `PAWAPAY_BASE_URL` changé pour `https://api.pawapay.io`
- [ ] Callback URL configurée avec HTTPS
- [ ] Tests passent en sandbox
- [ ] Au moins un paiement test réussi en sandbox
- [ ] Gestion des erreurs vérifiée
- [ ] Logs et monitoring configurés
- [ ] KYC complété sur le compte PawaPay
- [ ] Compte de production activé

## 9. Monitoring et débogage

### Logs

Tous les appels API sont loggés automatiquement:

```bash
php artisan pail --filter=PawaPay
```

### Dashboard PawaPay

Consultez toutes vos transactions sur:
- Sandbox: https://dashboard.sandbox.pawapay.io
- Production: https://dashboard.pawapay.io

### Statut de la plateforme

Vérifiez les pannes et maintenances:
https://status.pawapay.cloud/

## 10. Ressources

### Documentation
- 📖 [Documentation complète](PAWAPAY_INTEGRATION.md)
- 💻 [Exemples de code](app/Examples/PawapayUsageExample.php)
- 🧪 [Tests](tests/Feature/PawapayServiceTest.php)

### PawaPay
- 🌐 [Documentation officielle](https://docs.pawapay.io/v2/docs/welcome)
- 📚 [API Reference](https://docs.pawapay.io/v2/api-reference)
- 🔧 [Dashboard](https://dashboard.sandbox.pawapay.io)
- 📊 [Statut](https://status.pawapay.cloud/)
- 📧 Support: support@pawapay.io

## 11. Aide et support

### Erreurs courantes

**"could not find driver"**
→ MySQL/PDO non configuré, utilisez SQLite pour les tests

**"PAWAPAY_TOKEN not set"**
→ Vérifiez votre `.env` et relancez `php artisan config:clear`

**"Callback not received"**
→ Vérifiez que votre URL est accessible publiquement (ngrok en dev)

**"REJECTED: INVALID_PHONE_NUMBER"**
→ Vérifiez le format: chiffres uniquement, code pays, pas de zéro initial

### Contact

Pour toute question sur cette intégration, consultez:
1. `PAWAPAY_INTEGRATION.md` - Documentation complète
2. Skill PawaPay dans `.agents/skills/pawapay/`
3. Exemples dans `app/Examples/PawapayUsageExample.php`

---

**Bon développement! 🚀**
