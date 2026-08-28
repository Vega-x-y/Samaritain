# Contexte de maintenance PawaPay

## Objectif

Cette application Laravel intègre PawaPay v2 pour les paiements Mobile Money au Congo-Brazzaville.

Flux actuellement prévus :

- paiement d'un pass visite ;
- paiement d'un loyer par un tenant ;
- crédit du portefeuille de l'owner après paiement de loyer confirmé ;
- payout Mobile Money pour un owner ;
- vérification du statut par polling et commande de réconciliation ;
- aucun webhook actif dans cette version.

Les paiements artisans sont prévus plus tard, mais ne font pas partie du flux actuel.

## Configuration

La configuration principale est dans `config/services.php`, sous `services.pawapay`.

Variables attendues dans `.env` :

```env
PAWAPAY_MODE=sandbox
PAWAPAY_API_KEY=...
PAWAPAY_API_SANDBOX_URL=https://api.sandbox.pawapay.io/v2
PAWAPAY_API_PRODUCTION_URL=https://api.pawapay.io/v2
PAWAPAY_CURRENCY=XAF
PAWAPAY_COUNTRY=COG
PAWAPAY_DIAL_CODE=242
PAWAPAY_FEE_PERCENT=5
PAWAPAY_TIMEOUT=30
PAWAPAY_RETRY_TIMES=2
```

`api_url` est choisi selon `PAWAPAY_MODE`. Il contient déjà `/v2`. Le service ajoute ensuite seulement le chemin :

- `$apiUrl . '/deposits'` ;
- `$apiUrl . '/payouts'` ;
- `$apiUrl . '/active-conf'` ;
- `$apiUrl . '/deposits/{depositId}'` ;
- `$apiUrl . '/payouts/{payoutId}'`.

Exemple final attendu :

```text
https://api.sandbox.pawapay.io/v2/active-conf
```

Ne pas ajouter `/v2` dans les méthodes du service, sinon l'URL sera dupliquée.

La clé PawaPay ne doit jamais être loguée, committée ou exposée dans une vue. La clé qui était présente dans l'environnement précédent a été exposée : elle doit être révoquée et remplacée avant la production.

## Réponse `active-conf`

La réponse réelle est imbriquée :

```text
countries[]
  -> providers[]
      -> currencies[]
          -> operationTypes[]
```

Deux formes d'opération existent dans l'exemple PawaPay :

```json
{"DEPOSIT": {"status": "OPERATIONAL"}}
```

et :

```json
{"operationType": "PAYOUT", "status": "DELAYED"}
```

`PawapayService::activeProviders(string $operationType = 'DEPOSIT')` :

1. appelle `/active-conf` ;
2. sélectionne le pays `COG` ;
3. sélectionne la devise `XAF` ;
4. sélectionne l'opération demandée ;
5. ne retourne que les opérations dont le statut est `OPERATIONAL` ;
6. retourne un tableau associatif `PROVIDER_CODE => DISPLAY_NAME`.

Les contrôleurs convertissent ensuite ce tableau en format destiné aux vues :

```php
$payment_config = [
    'providers' => collect($providers)
        ->map(fn (string $displayName, string $provider): array => compact('provider', 'displayName'))
        ->values()
        ->all(),
];
```

Les vues utilisent donc :

```blade
@foreach ($payment_config['providers'] as $item)
    <option value="{{ $item['provider'] }}">
        {{ $item['displayName'] }}
    </option>
@endforeach
```

Important : l'exemple fourni avec `BEN` et `XOF` est un exemple générique. Il ne donnera aucun opérateur pour la configuration actuelle `COG/XAF`. Il faut tester avec la réponse correspondant réellement au compte PawaPay et à `COG/XAF`.

## Service PawaPay

Fichier : `app/Services/PawapayService.php`.

Méthodes publiques principales :

```php
initiateDeposit(DepositRequest $request): array
initiatePayout(PayoutRequest $request): array
getDepositStatus(string $depositId): array
getPayoutStatus(string $payoutId): array
getActiveConfiguration(): array
activeProviders(string $operationType = 'DEPOSIT'): array
normalizePhoneNumber(string $phoneNumber): string
amountAfterFee(int $amountInMinorUnits): int
```

Les DTO sont dans :

- `app/DataTransferObjects/Pawapay/DepositRequest.php` ;
- `app/DataTransferObjects/Pawapay/PayoutRequest.php`.

Les payloads v2 utilisent :

- dépôt : `depositId`, `payer.type`, `payer.accountDetails.phoneNumber`, `payer.accountDetails.provider`, `amount`, `currency` ;
- payout : `payoutId`, `recipient.type`, `recipient.accountDetails.phoneNumber`, `recipient.accountDetails.provider`, `amount`, `currency`.

Les montants envoyés à PawaPay sont des chaînes décimales. Les montants locaux sont actuellement stockés en unités mineures dans `Transaction::amount`; vérifier cette convention avant tout changement.

## Téléphone et frais

`normalizePhoneNumber()` :

- retire espaces, signes et caractères non numériques ;
- transforme un numéro local commençant par `0` en numéro préfixé par `242` ;
- ajoute `242` si le préfixe est absent ;
- PawaPay reçoit donc un numéro sans `+`, au format `242XXXXXXXXX`.

Commission configurée : `5 %`.

`amountAfterFee()` calcule :

```text
montant net = montant saisi * 95 / 100
```

Le calcul utilise des entiers et `intdiv`.

Décision métier actuelle :

- dépôt de 100 -> montant crédité/traité localement de 95 ;
- payout demandé de 100 -> montant envoyé/reçu de 95.

Vérifier avec le métier si PawaPay facture réellement la commission en plus, car cette règle peut différer du contrat API réel.

## Paiement pass visite

Contrôleur : `app/Http/Controllers/UserVisitPassController.php`.

Routes :

- `GET /my-visit-passes/{visitPass}/pay` : affiche le formulaire ;
- `POST /my-visit-passes/{visitPass}/initiate-payment` : crée et initie le dépôt.

Vue : `resources/views/visit-passes/pay.blade.php`.

Le GET :

- autorise l'accès au pass ;
- refuse un pass déjà payé ;
- appelle `activeProviders()` ;
- passe `payment_config`, `currency` et `providerError` à la vue.

Le POST :

- génère l'identifiant UUID avant l'appel API ;
- crée la transaction `DEPOSIT` avant l'appel API ;
- lie la transaction au `VisitPass` ;
- normalise le téléphone ;
- initie le dépôt ;
- redirige vers `transactions.pending`.

## Paiement de loyer

Contrôleur : `app/Http/Controllers/Tenant/DashboardController.php`.

Méthodes restaurées :

- `payments()` ;
- `payRentPayment()` ;
- `initiateRentPayment()`.

Le tenant est autorisé uniquement si :

- l'e-mail du contrat correspond à l'utilisateur authentifié ;
- le contrat est `active`.

Le montant est lu côté serveur depuis `RentPayment::amount_due`. Le client ne doit jamais pouvoir choisir un montant arbitraire.

La transaction est liée à `rent_payment_id`. Après un statut `COMPLETED`, `RentPaymentService` marque le loyer payé et génère le reçu.

Vue : `resources/views/pages/tenant/rent-pay.blade.php`.

## Ledger owner

Modèles :

- `app/Models/OwnerWallet.php` ;
- `app/Models/WalletEntry.php`.

Service : `app/Services/OwnerWalletService.php`.

Migrations :

- `database/migrations/2026_08_28_092353_create_owner_wallets_table.php` ;
- `database/migrations/2026_08_28_092356_create_wallet_entries_table.php`.

Structure :

```text
owner_wallets
  owner_id unique
  available_balance
  reserved_balance

wallet_entries
  owner_wallet_id
  transaction_id
  kind
  amount
  metadata
  unique(transaction_id, kind)
```

Types d'écritures utilisés :

- `rent_credit` ;
- `payout_reservation` ;
- `payout_debit` ;
- `payout_release`.

`OwnerWalletService` verrouille le portefeuille avec `lockForUpdate()` dans une transaction DB.

Settlement :

- dépôt de loyer `COMPLETED` : crédit de l'owner de la propriété ;
- payout `PENDING/ACCEPTED` : montant réservé ;
- payout `COMPLETED` : réservation débitée définitivement ;
- payout `FAILED`, `REJECTED` ou `CANCELLED` : réservation libérée ;
- une clé unique par transaction et type d'écriture empêche les doubles effets.

Le crédit owner utilise actuellement `Transaction::amount`, donc le crédit est le montant net après frais.

## Payout owner

Contrôleur : `app/Http/Controllers/Owner/PayoutController.php`.

Routes :

- `GET /owner/payouts` ;
- `GET /owner/payouts/create` ;
- `POST /owner/payouts`.

Le contrôleur :

- récupère les opérateurs avec `activeProviders('PAYOUT')` ;
- valide le fournisseur parmi les opérateurs actifs ;
- crée la transaction payout ;
- réserve le solde avant l'appel PawaPay ;
- supprime la transaction si le solde est insuffisant ;
- appelle `initiatePayout()` ;
- settle immédiatement seulement si la réponse est finale.

Le formulaire affiche le solde disponible dans `resources/views/pages/owner/payouts/create.blade.php`.

Un payout `DELAYED` retourné par `active-conf` est actuellement exclu, car seul `OPERATIONAL` est accepté. Confirmer avec le métier si `DELAYED` doit rester sélectionnable avec avertissement.

## Statut et polling

Contrôleur : `app/Http/Controllers/TransactionController.php`.

Routes actives :

- `GET /transactions/{transaction}/pending` ;
- `GET /transactions/{transaction}/status` ;
- `GET /tenant/transactions` ;
- `GET /tenant/transactions/{transaction}`.

Les routes webhook/callback ont été retirées de la surface active. Aucun webhook PawaPay ne doit être ajouté sans implémenter auparavant la vérification de signature et l'idempotence.

Le statut manuel :

1. vérifie que la transaction appartient à l'utilisateur connecté ;
2. appelle l'endpoint dépôt ou payout selon le type ;
3. met à jour la transaction ;
4. si le statut est final, appelle `OwnerWalletService::settle()` ;
5. finalise le pass ou le loyer.

Commande : `app/Console/Commands/ReconcilePawaPayPaymentsCommand.php`.

Commande d'exécution :

```bash
php artisan pawapay:reconcile --threshold=15
```

Elle traite les transactions non finales et appelle l'endpoint de statut approprié.

## Bugs ou risques connus à vérifier

1. **Tests DB indisponibles localement** : l'environnement actuel n'a pas de driver PDO SQLite ni de connexion MySQL disponible. Les tests Pest échouent avant les assertions avec `could not find driver`.
2. **Migrations non exécutées localement** : `php artisan migrate --pretend` échoue également sans driver/connexion DB. Exécuter les migrations dans l'environnement Docker ou Railway avant le test fonctionnel.
3. **Normalisation de réponse de statut** : vérifier si PawaPay renvoie un wrapper `{status: "FOUND", data: {status: "COMPLETED"}}`. Le contrôleur actuel lit directement `response['status']`; si le wrapper est utilisé, il faut normaliser vers `data.status` dans `PawapayService`.
4. **Settlement dupliqué dans la réconciliation** : la commande met d'abord à jour la transaction puis appelle `OwnerWalletService::settle()`, qui la met à jour à nouveau. Ce n'est pas dangereux pour le ledger, mais peut être simplifié.
5. **Pass déjà initié** : vérifier qu'un second POST ne peut pas créer deux dépôts pour le même pass. Ajouter une recherche de transaction pending/existante ou une contrainte si nécessaire.
6. **Loyer déjà associé** : vérifier qu'un second POST ne remplace pas `RentPayment::transaction_id` avec une nouvelle transaction pending.
7. **Solde initial owner** : le payout est refusé si `available_balance` vaut zéro. Il faut d'abord effectuer un paiement de loyer confirmé ou ajouter un mécanisme d'ouverture/backfill du solde.
8. **Permission owner** : les routes utilisent le middleware `owner`, mais la permission Spatie dédiée `payouts.create` n'est pas encore imposée explicitement dans le contrôleur.
9. **Statut `DELAYED`** : un payout delayed est exclu de la liste opérateurs. Décider s'il doit être affiché comme disponible avec avertissement.
10. **Réponse active-conf sans `COG/XAF`** : une réponse exemple pour `BEN/XOF` doit produire une liste vide avec la configuration actuelle, ce qui est normal.
11. **Erreur d'opérateurs** : le formulaire pass gère l'exception PawaPay ; le formulaire loyer et le formulaire payout devraient aussi gérer proprement l'échec de `/active-conf` au lieu de produire une erreur 500.
12. **Frais PawaPay** : confirmer que le montant net de 5 % correspond au contrat PawaPay. Le code actuel ne conserve pas encore un champ séparé pour montant brut, frais et montant net.
13. **Vue globale** : `php artisan view:cache` rencontre une erreur indépendante dans une vue existante utilisant `flux::table.head`. Ne pas attribuer automatiquement cette erreur au flux PawaPay.
14. **Fichier temporaire** : vérifier le fichier non suivi `storage/framework/lsp-*.php` avant commit ; il est probablement généré par l'analyseur et ne doit pas être versionné.

## Vérifications recommandées pour une autre IA

```bash
php -l app/Services/PawapayService.php
php -l app/Services/OwnerWalletService.php
php -l app/Http/Controllers/TransactionController.php
php -l app/Http/Controllers/Owner/PayoutController.php
php -l app/Http/Controllers/Tenant/DashboardController.php
php artisan route:list --except-vendor
vendor/bin/pint --dirty --format agent
git diff --check
```

Avec une base DB disponible :

```bash
php artisan migrate --no-interaction
php artisan test --compact tests/Feature/PawapayServiceTest.php
php artisan test --compact tests/Feature/PayoutControllerTest.php
php artisan test --compact tests/Feature/RentPaymentTest.php
```

Tester avec `Http::fake()` au minimum :

- URL exacte `/v2/active-conf` ;
- extraction `COG/XAF` ;
- dépôt et payout `OPERATIONAL` ;
- rejet d'un opérateur d'un autre pays/devise ;
- téléphone local `06...` -> `2426...` ;
- montant net 95 % ;
- statut `FOUND/data.status` ;
- settlement répété sans double écriture ;
- payout refusé sans solde négatif ;
- libération après `FAILED` ;
- isolation d'une transaction appartenant à un autre utilisateur.

## Fichiers centraux

- `config/services.php`
- `config/pawapay.php`
- `app/Services/PawapayService.php`
- `app/Services/OwnerWalletService.php`
- `app/Services/RentPaymentService.php`
- `app/Models/Transaction.php`
- `app/Models/OwnerWallet.php`
- `app/Models/WalletEntry.php`
- `app/Http/Controllers/UserVisitPassController.php`
- `app/Http/Controllers/Tenant/DashboardController.php`
- `app/Http/Controllers/Owner/PayoutController.php`
- `app/Http/Controllers/TransactionController.php`
- `app/Console/Commands/ReconcilePawaPayPaymentsCommand.php`
- `resources/views/visit-passes/pay.blade.php`
- `resources/views/pages/tenant/rent-pay.blade.php`
- `resources/views/pages/owner/payouts/create.blade.php`
- `database/migrations/2026_08_28_092353_create_owner_wallets_table.php`
- `database/migrations/2026_08_28_092356_create_wallet_entries_table.php`
