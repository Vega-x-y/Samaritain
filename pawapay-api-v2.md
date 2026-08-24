# PawaPay Merchant API v2 — Documentation

> Compilé depuis la documentation officielle PawaPay : https://docs.pawapay.io/v2/api-reference
> (© PawaPay — reproduit ici comme référence technique pour intégration)
> Date de compilation : 24 août 2026

## Sommaire

1. [Vue d'ensemble](#1-vue-densemble)
2. [Authentification](#2-authentification)
3. [Fonctionnement asynchrone & callbacks](#3-fonctionnement-asynchrone--callbacks)
4. [Deposits (encaissements)](#4-deposits-encaissements)
5. [Payouts (paiements sortants)](#5-payouts-paiements-sortants)
6. [Refunds (remboursements)](#6-refunds-remboursements)
7. [Remittances](#7-remittances)
8. [Checkouts](#8-checkouts)
9. [Split Payments](#9-split-payments)
10. [Payment Page](#10-payment-page)
11. [Finances](#11-finances)
12. [Toolkit](#12-toolkit)
13. [Codes d'erreur communs](#13-codes-derreur-communs)
14. [Aller en production](#14-aller-en-production)

---

## 1. Vue d'ensemble

L'API PawaPay Merchant permet d'encaisser et de payer via Mobile Money en Afrique subsaharienne, avec une API unique connectée aux opérateurs mobiles (MTN, Airtel, Orange, M-Pesa, Vodacom, Moov, Wave, Zamtel, etc.).

### URLs de base

| Environnement | Base URL API | Dashboard |
|---|---|---|
| Sandbox | `https://api.sandbox.pawapay.io` | `https://dashboard.sandbox.pawapay.io` |
| Production | `https://api.pawapay.io` | `https://dashboard.pawapay.io` |

Ces URLs sont différentes entre sandbox et production — à stocker en configuration par environnement.

Le sandbox est accessible immédiatement à la création du compte, sans KYC, isolé de la production (aucun argent réel). L'accès production est débloqué après onboarding/KYC complet.

### Rétrocompatibilité

L'API est toujours rétrocompatible mais ne doit pas être strictement validée contre un schéma strict, car des changements rétrocompatibles peuvent être introduits (nouveaux champs, etc.).

---

## 2. Authentification

Toutes les requêtes utilisent un **bearer token** dans l'en-tête `Authorization`.

```
Authorization: Bearer <YOUR_API_TOKEN>
```

Le token se génère depuis le Dashboard PawaPay : *System Configuration → API Tokens*. Les tokens sandbox et production sont différents et non interchangeables.

Exemple :

```bash
curl -i -X POST \
  https://api.sandbox.pawapay.io/v2/payouts \
  -H 'Authorization: Bearer <YOUR_API_TOKEN>' \
  -H 'Content-Type: application/json' \
  -d '{
    "payoutId": "33f30946-881d-40bc-8ca2-94aa4cd467ac",
    "amount": "15",
    "currency": "ZMW",
    "recipient": {
      "type": "MMO",
      "accountDetails": {
        "phoneNumber": "260763456789",
        "provider": "MTN_MOMO_ZMB"
      }
    }
  }'
```

### Signatures (RFC-9421) — sécurité renforcée, optionnelle

Pour les appels financiers, des en-têtes optionnels liés à la signature peuvent être envoyés (uniquement requis si "Only accept signed requests" est activé sur le dashboard) :

| Header | Description |
|---|---|
| `Content-Digest` | Hash SHA-256 ou SHA-512 du corps de la requête |
| `Signature` | Signature de la requête selon RFC-9421 |
| `Signature-Input` | Input de signature selon RFC-9421 |
| `Accept-Signature` | Algorithme de signature attendu pour la réponse (RFC-9421) |
| `Accept-Digest` | Algorithme de digest attendu pour la réponse (RFC-9421) |

Récupérer les clés publiques de PawaPay via `GET /v2/public-keys` (Toolkit → Public Keys) pour vérifier les signatures de leurs callbacks. Détails complets : https://docs.pawapay.io/v2/docs/signatures

---

## 3. Fonctionnement asynchrone & callbacks

L'API est **asynchrone**. Chaque appel d'initiation (deposit/payout/refund/remittance) retourne immédiatement un statut d'acceptation (`ACCEPTED` / `REJECTED` / `DUPLICATE_IGNORED`), pas le résultat final.

Pour connaître le statut final :
1. **Callback** (recommandé) — configuré depuis le Dashboard (*System Configuration → Callback URLs*). PawaPay `POST`e le statut final à l'URL configurée.
2. **Polling** — via l'endpoint `Check status` correspondant (`GET /v2/{deposits|payouts|refunds|remittances}/{id}`).

### Bonnes pratiques pour les callbacks

- L'endpoint de callback doit être **idempotent** (un même callback peut arriver plusieurs fois — dédupliquer par ID).
- Doit accepter les requêtes `POST`.
- Utiliser un certificat SSL d'une CA de confiance.
- PawaPay tentera la livraison pendant **15 minutes**.
- Répondre `HTTP 200 OK` pour acquitter la réception.
- En cas d'échec de livraison, déclencher un renvoi via l'endpoint `Resend callback` correspondant, ou manuellement depuis le Dashboard.
- Exclure l'endpoint de callback de l'authentification applicative standard.

### IPs à whitelister (callbacks entrants de PawaPay)

| Environnement | IP |
|---|---|
| Sandbox | 3.64.89.224/32 |
| Production | 18.192.208.15/32 |
| Production | 18.195.113.136/32 |
| Production | 3.72.212.107/32 |
| Production | 54.73.125.42/32 |
| Production | 54.155.38.214/32 |
| Production | 54.73.130.113/32 |

### Statuts intermédiaires notables

- `IN_RECONCILIATION` : PawaPay n'a pas pu déterminer immédiatement le statut final ; le moteur de réconciliation automatique s'en charge — aucune action requise de votre part.
- `PROCESSING` : en cours de traitement (ex. authentification en cours).
- Réponse HTTP 500 avec `failureCode: UNKNOWN_ERROR` : le statut n'est **pas garanti** avoir échoué — toujours vérifier via `Check status` avant de considérer un paiement comme `FAILED`.

---

## 4. Deposits (encaissements)

Un *deposit* transfère des fonds du portefeuille Mobile Money du client vers votre compte PawaPay.

### `POST /v2/deposits` — Initiate deposit

Idempotent : rejouer la requête avec le même `depositId` renvoie `DUPLICATE_IGNORED`.

**Requête :**

```json
POST https://api.sandbox.pawapay.io/v2/deposits

{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "payer": {
    "type": "MMO",
    "accountDetails": {
      "phoneNumber": "260763456789",
      "provider": "MTN_MOMO_ZMB"
    }
  },
  "amount": "15",
  "currency": "ZMW",
  "preAuthorisationCode": "<string>",
  "clientReferenceId": "INV-123456",
  "customerMessage": "Note of 4 to 22 chars",
  "metadata": [
    { "orderId": "ORD-123456789" },
    { "customerId": "customer@email.com", "isPII": true }
  ]
}
```

**Champs :**

| Champ | Type | Requis | Description |
|---|---|---|---|
| `depositId` | UUIDv4 | oui | ID unique généré par vous, à stocker **avant** l'appel. Base de l'idempotence. |
| `payer.type` | string | oui | `MMO` (seule valeur actuellement supportée). |
| `payer.accountDetails.phoneNumber` | string (MSISDN) | oui | Chiffres uniquement, sans `+`, sans zéro initial, code pays obligatoire. |
| `payer.accountDetails.provider` | string | oui | Code opérateur (voir [Providers](https://docs.pawapay.io/v2/docs/providers)). |
| `amount` | string | oui | Pas de zéros de tête (sauf < 1). Certains providers n'acceptent pas les décimales — voir Active Configuration. |
| `currency` | string (ISO 4217) | oui | Doit être supportée par le provider. |
| `preAuthorisationCode` | string | non | Requis pour les providers avec `authType: PREAUTH` (ex. Orange Burkina Faso) — token OTP obtenu par le client via USSD avant l'appel. |
| `clientReferenceId` | string | non | Référence interne (facture, commande...). |
| `customerMessage` | string, 4–22 caractères alphanumériques | non | Visible par le client selon l'opérateur (SMS, historique). Par défaut, votre nom d'entreprise. |
| `metadata` | array (max 10 items) | non | Objets `{clé: valeur, isPII?: bool}` — visibles dans le dashboard, les relevés et les callbacks (jamais visibles par le client). |

**Réponse (200) :**

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "status": "ACCEPTED",
  "nextStep": "FINAL_STATUS",
  "created": "2020-10-19T11:17:01Z"
}
```

`status` possibles : `ACCEPTED` (callback à venir), `REJECTED` (voir `failureReason`, pas de callback), `DUPLICATE_IGNORED` (pas de callback).

`nextStep` indique la suite du flux :
- `FINAL_STATUS` — attendre le callback / poller le statut final (flux PIN classique).
- `GET_AUTH_URL` — cas des providers à authentification par redirection (ex. Wave Sénégal/Côte d'Ivoire) : il faut poller `Check deposit status` jusqu'à obtenir `REDIRECT_TO_AUTH_URL` et une `authorizationUrl` vers laquelle rediriger le client.

**Codes d'échec spécifiques au dépôt** (`failureReason.failureCode`) : `DEPOSITS_NOT_ALLOWED`, `INVALID_PHONE_NUMBER`, `INVALID_AMOUNT`, `AMOUNT_OUT_OF_BOUNDS`, `INVALID_CURRENCY`, `INVALID_PROVIDER`, `PROVIDER_TEMPORARILY_UNAVAILABLE`, `DUPLICATE_METADATA_FIELD` — en plus des [codes communs](#13-codes-derreur-communs).

### `GET /v2/deposits/{depositId}` — Check deposit status

```json
{
  "status": "FOUND",
  "data": {
    "depositId": "8917c345-4791-4285-a416-62f24b6982db",
    "status": "COMPLETED",
    "amount": "123.00",
    "currency": "ZMW",
    "country": "ZMB",
    "payer": {
      "type": "MMO",
      "accountDetails": { "phoneNumber": "260763456789", "provider": "MTN_MOMO_ZMB" }
    },
    "customerMessage": "To ACME company",
    "clientReferenceId": "REF-987654321",
    "created": "2020-10-19T08:17:01Z",
    "providerTransactionId": "12356789",
    "metadata": { "orderId": "ORD-123456789", "customerId": "customer@email.com" }
  }
}
```

`status` de l'enveloppe : `FOUND` / `NOT_FOUND`. `data.status` (statut du deposit) : `ACCEPTED`, `PROCESSING`, `COMPLETED`, `FAILED`, `IN_RECONCILIATION`.

En cas de `FAILED`, `failureReason.failureCode` peut être notamment `PAYMENT_NOT_APPROVED` (client n'a pas validé) ainsi que les codes communs.

### `POST /v2/deposits/resend-callback/{depositId}` — Resend deposit callback

Renvoie le callback pour un deposit ayant atteint un statut final.

```json
// Succès
{ "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639", "status": "ACCEPTED" }
// Échecs possibles
{ "status": "REJECTED", "failureReason": { "failureCode": "NOT_FOUND", "failureMessage": "Payout with ID ... not found" } }
{ "status": "REJECTED", "failureReason": { "failureCode": "INVALID_STATE", "failureMessage": "... has not finished processing" } }
```

### Deposit callback (webhook entrant chez vous)

Si des callbacks sont configurés, PawaPay `POST`e ce payload à votre URL configurée lorsque le deposit atteint un statut final :

```json
{
  "depositId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "status": "COMPLETED",
  "requestedAmount": "15",
  "depositedAmount": "15",
  "currency": "ZMW",
  "country": "ZMB",
  "correspondent": "MTN_MOMO_ZMB",
  "payer": { "type": "MSISDN", "address": { "value": "260763456789" } },
  "created": "2020-02-21T17:32:29Z",
  "respondedByPayer": "2020-02-21T17:32:30Z",
  "providerTransactionId": "12356789",
  "metadata": { "orderId": "ORD-123456789" }
}
```

Si des signatures sont activées, les en-têtes `Content-Digest`, `Signature`, `Signature-Input`, `X-Signature-Timestamp` sont inclus — à vérifier avec les clés publiques PawaPay.

---

## 5. Payouts (paiements sortants)

Un *payout* transfère des fonds de votre compte PawaPay vers le portefeuille Mobile Money d'un client — même logique et authentification que les deposits.

### `POST /v2/payouts` — Initiate payout

```json
POST https://api.sandbox.pawapay.io/v2/payouts

{
  "payoutId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "amount": "15",
  "currency": "ZMW",
  "recipient": {
    "type": "MMO",
    "accountDetails": {
      "phoneNumber": "260763456789",
      "provider": "MTN_MOMO_ZMB"
    }
  },
  "clientReferenceId": "INV-123456",
  "customerMessage": "Note of 4 to 22 chars",
  "metadata": [{ "orderId": "ORD-123456789" }]
}
```

**Réponse (200) :**

```json
{ "payoutId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639", "status": "ACCEPTED", "created": "2020-10-19T11:17:01Z" }
```

`status` : `ACCEPTED`, `ENQUEUED` (mis en file — ex. le provider est `DELAYED`, sera traité dès résolution), `REJECTED`, `DUPLICATE_IGNORED`.

### `POST /v2/payouts/bulk` — Initiate bulk payouts

Envoie plusieurs payouts en un seul appel (même schéma que `PayoutCreationRequest`, répété en tableau). Réponse : tableau de statuts individuels par `payoutId` (réponse « mixte » possible : certains `ACCEPTED`, d'autres `REJECTED`/`DUPLICATE_IGNORED`).

```json
[
  { "payoutId": "...1", "status": "ACCEPTED", "created": "2020-10-19T11:17:01Z" },
  { "payoutId": "...2", "status": "DUPLICATE_IGNORED", "created": "2020-10-19T10:22:49Z" },
  { "payoutId": "...3", "status": "REJECTED", "failureReason": { "failureCode": "AMOUNT_TOO_LARGE", "failureMessage": "Amount should not be greater than 1000" } }
]
```

### `GET /v2/payouts/{payoutId}` — Check payout status

Même structure que pour les deposits (`FOUND`/`NOT_FOUND`, puis `data.status` parmi `ACCEPTED`, `PROCESSING`, `COMPLETED`, `FAILED`, `IN_RECONCILIATION`).

### `POST /v2/payouts/resend-callback/{payoutId}` — Resend payout callback

Le payout doit avoir atteint un état final. Mêmes réponses/erreurs (`ACCEPTED`, `NOT_FOUND`, `INVALID_STATE`) que pour les deposits.

### `POST /v2/payouts/fail-enqueued/{payoutId}` — Cancel enqueued payout

Annule un payout `ENQUEUED` (mis en file d'attente, ex. hors plafond instantané ou provider `DELAYED`) tant qu'il n'a pas encore été traité.

```json
// Succès
{ "payoutId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639", "status": "ACCEPTED" }
// Échec — pas enqueued
{ "status": "REJECTED", "failureReason": { "failureCode": "INVALID_STATE", "failureMessage": "Payout with ID ... is not enqueued" } }
```

### Payout callback

Même principe que le deposit callback, envoyé lorsque le payout atteint un statut final.

---

## 6. Refunds (remboursements)

Rembourse tout ou partie d'un deposit déjà `COMPLETED`.

### `POST /v2/refunds` — Initiate refund

```json
POST https://api.sandbox.pawapay.io/v2/refunds

{
  "refundId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "depositId": "8917c345-4791-4285-a416-62f24b6982db",
  "amount": "15"
}
```

`amount` est optionnel : si omis, remboursement total du deposit. Sinon, remboursement partiel.

**Réponse (200) :** `status` parmi `ACCEPTED`, `DUPLICATE_IGNORED`, `REJECTED`.

**Codes de rejet spécifiques :**
- `NOT_FOUND` — le deposit référencé n'existe pas.
- `INVALID_STATE` — le deposit n'a pas encore atteint un statut final (n'est pas `COMPLETED`).
- Ainsi que les [codes communs](#13-codes-derreur-communs) (`INVALID_AMOUNT`, `AMOUNT_OUT_OF_BOUNDS`, `INVALID_CURRENCY`, `PROVIDER_TEMPORARILY_UNAVAILABLE`, etc.)

### `GET /v2/refunds/{refundId}` — Check refund status

Même structure `FOUND`/`NOT_FOUND` puis `data.status` (`ACCEPTED`, `PROCESSING`, `COMPLETED`, `FAILED`, `IN_RECONCILIATION`), avec `recipient` (au lieu de `payer`) décrivant le bénéficiaire du remboursement.

### `POST /v2/refunds/resend-callback/{refundId}` — Resend refund callback

Identique en principe aux endpoints resend des deposits/payouts.

### Refund callback

Callback envoyé à statut final, structure analogue aux deposit/payout callbacks.

---

## 7. Remittances

Fonctionnent comme les payouts (fonds envoyés depuis votre compte PawaPay vers un bénéficiaire), utilisées typiquement pour des cas d'usage de transfert d'argent / remise.

### `POST /v2/remittances` — Initiate remittance

```json
POST https://api.sandbox.pawapay.io/v2/remittances

{
  "remittanceId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639",
  "amount": "15",
  "currency": "ZMW",
  "recipient": {
    "type": "MMO",
    "accountDetails": { "phoneNumber": "260763456789", "provider": "MTN_MOMO_ZMB" }
  }
}
```

`status` de réponse : `ACCEPTED`, `DUPLICATE_IGNORED`, `REJECTED` — mêmes codes d'échec que les payouts (`PROVIDER_TEMPORARILY_UNAVAILABLE`, `INVALID_PHONE_NUMBER`, `INVALID_CURRENCY`, `INVALID_AMOUNT`, `AMOUNT_OUT_OF_BOUNDS`, etc.)

### `POST /v2/remittances/bulk` — Initiate bulk remittances

Analogue à `POST /v2/payouts/bulk`, tableau de statuts en retour.

### `GET /v2/remittances/{remittanceId}` — Check remittance status

Même structure que payouts/deposits.

### `POST /v2/remittances/resend-callback/{remittanceId}` — Resend remittance callback

### `POST /v2/remittances/fail-enqueued/{remittanceId}` — Cancel enqueued remittance

Annule une remittance `ENQUEUED` non encore traitée (mêmes réponses que l'annulation de payout).

### Remittance callback

Contient les mêmes en-têtes de signature optionnels que les autres callbacks (`Content-Digest`, `Signature`, `Signature-Input`, `X-Signature-Timestamp`, `Accept-Signature`).

---

## 8. Checkouts

Un *checkout* est un widget de paiement hébergé qui suit l'intégralité d'une intention de paiement (potentiellement plusieurs tentatives de deposit) via un seul objet.

### `POST /v2/checkouts` — Initiate checkout

**Réponse (200) — exemple `ACCEPTED` :**

```json
{
  "checkoutId": "afb57b93-7849-49aa-babb-4c3ccbfe3d79",
  "status": "ACCEPTED",
  "redirectUrl": "https://checkout.sandbox.pawapay.io/7mVk1x8UbTamQ64xGR",
  "created": "2026-03-27T10:30:00Z",
  "expiresAt": "2026-03-27T11:30:00Z",
  "checkoutCode": "7mVk1x8UbTamQ64xGR"
}
```

Le client doit être redirigé vers `redirectUrl` pour compléter le paiement.

### `GET /v2/checkouts/{checkoutId}` — Check checkout status

`data.status` possibles : `WAITING_PAYMENT`, `COMPLETED`, `FAILED`, `EXPIRED`, `CANCELLED`.

Réponse `COMPLETED` (extrait) :

```json
{
  "status": "FOUND",
  "data": {
    "checkoutId": "afb57b93-7849-49aa-babb-4c3ccbfe3d79",
    "status": "COMPLETED",
    "returnUrl": "https://merchant.example.com/checkout-result",
    "returnMethod": "INSTANT",
    "defaultLanguage": "en",
    "countries": ["ZMB"],
    "amounts": [{ "country": "ZMB", "currency": "ZMW", "amount": "100" }],
    "payer": {
      "type": "MMO",
      "accountDetails": { "phoneNumber": "260973024434", "provider": "MTN_MOMO_ZMB", "allowCustomerToOverride": true }
    },
    "depositStatus": "COMPLETED",
    "deposit": { "depositId": "eac4d2f3-...", "status": "COMPLETED", "...": "..." },
    "depositsHistory": [ "..." ],
    "checkoutCode": "7mVk1x8UbTamQ64xGR"
  }
}
```

`depositsHistory` liste toutes les tentatives de deposit associées à ce checkout (un checkout peut englober plusieurs tentatives avant réussite).

### `POST /v2/checkouts/{checkoutId}/expire` — Expire checkout

Force l'expiration manuelle d'un checkout.

```json
{
  "checkoutId": "afb57b93-7849-49aa-babb-4c3ccbfe3d79",
  "status": "EXPIRED",
  "expiredAt": "2026-03-27T10:45:00Z",
  "reason": "MANUAL_EXPIRY",
  "expiredBy": "API"
}
```

Erreur `404 NOT_FOUND` si le checkout n'existe pas.

### Checkout callback

Callback envoyé lorsque le checkout atteint un statut final (`COMPLETED`, `FAILED`, `EXPIRED`, `CANCELLED`).

---

## 9. Split Payments

Permet d'encaisser un paiement client puis de le répartir automatiquement (split) vers un ou plusieurs bénéficiaires via des payouts liés.

### `POST /v2/split-payments` — Initiate split payment

**Réponse (200) :**

```json
{ "splitPaymentId": "f4401bd2-1568-4140-bf2d-eb77d2b2b639", "status": "ACCEPTED", "created": "2025-01-15T10:30:00Z" }
```

Codes d'échec spécifiques : `PAYOUT_EXCEEDS_DEPOSIT` (le total des splits dépasse le montant du deposit), `SPLIT_PAYMENTS_NOT_ALLOWED` (fonctionnalité non activée pour ce provider sur votre compte), en plus des codes communs.

### `GET /v2/split-payments/{splitPaymentId}` — Check split payment status

`data.status` possibles : `COMPLETED`, `PAYOUT_FAILED` (le deposit a réussi mais un ou plusieurs splits ont échoué), `FAILED`.

```json
{
  "status": "FOUND",
  "data": {
    "splitPaymentId": "8917c345-4791-4285-a416-62f24b6982db",
    "status": "COMPLETED",
    "amount": "100.00",
    "currency": "ZMW",
    "payer": { "type": "MMO", "accountDetails": { "phoneNumber": "260973024434", "provider": "MTN_MOMO_ZMB" } },
    "splits": [
      {
        "payoutId": "6f53f5f3-2f97-4879-8ed6-50072fe9d2fc",
        "status": "COMPLETED",
        "amount": "100.00",
        "recipient": { "type": "MMO", "accountDetails": { "phoneNumber": "260973024456", "provider": "MTN_MOMO_ZMB" } }
      }
    ]
  }
}
```

---

## 10. Payment Page

Page de paiement hébergée par PawaPay où le client choisit son opérateur et saisit son numéro — évite d'avoir à construire son propre formulaire.

### `POST /v2/paymentpage` — Deposit via Payment Page

```json
POST https://api.sandbox.pawapay.io/v2/paymentpage

{
  "depositId": "695776cf-73ba-42ff-b9cb-2b9acc008e22",
  "returnUrl": "https://merchant.com/returnUrl",
  "amount": "15",
  "currency": "ZMW",
  "reason": "Demo payment"
}
```

- `depositId` : UUIDv4 généré par vous, à stocker avant l'appel.
- `returnUrl` : URL de redirection du client après le paiement (le `depositId` original y est passé en query param).
- `reason` : optionnel, affiché au client sur la page de paiement.

**Réponse :** un `redirectUrl` vers lequel rediriger le client pour démarrer le paiement.

**Flux complet :**
1. Appeler cet endpoint → récupérer `redirectUrl`.
2. Rediriger le client vers `redirectUrl`.
3. Une fois le paiement terminé, le client est redirigé vers `returnUrl` (avec `depositId` en query param).
4. Sur `returnUrl`, **toujours confirmer le statut** via callback ou `GET /v2/deposits/{depositId}` — ne jamais se fier uniquement à la redirection.

Si le client abandonne, le deposit reste `NOT_FOUND` puis doit être considéré `FAILED` après expiration de la session (~15 minutes).

Codes d'échec identiques à `initiate-deposit` (`PROVIDER_TEMPORARILY_UNAVAILABLE`, `INVALID_PHONE_NUMBER`, `INVALID_CURRENCY`, `INVALID_AMOUNT`, `AMOUNT_OUT_OF_BOUNDS`, etc.)

---

## 11. Finances

### `GET /v2/wallet-balances` — Wallet balances

Liste les portefeuilles et soldes configurés sur votre compte.

Paramètre optionnel : `country` (filtre ISO 3166-1 alpha-3).

```json
{
  "balances": [
    { "country": "ZMB", "balance": "21798.03", "currency": "ZMW", "provider": "" },
    { "country": "UGA", "balance": "10798.03", "currency": "UGX", "provider": "" }
  ]
}
```

`provider` n'est renseigné que si le portefeuille est spécifique à un seul opérateur.

### `POST /v2/statements` — Initiate statement (relevé financier)

Génère un relevé financier (asynchrone comme les paiements — vérifier le statut puis récupérer via `Check statement status`).

### `GET /v2/statements/{statementId}` — Check statement status

### Statement callback

Callback envoyé une fois le relevé prêt.

---

## 12. Toolkit

### `GET /v2/active-conf` — Active Configuration

Renvoie toute la configuration active de votre compte : pays et providers activés, devises supportées, types d'authentification, limites min/max de transaction, instructions d'authentification à afficher au client, URLs de callback configurées, etc. **Source de vérité** à interroger dynamiquement plutôt que de coder en dur une liste de providers.

Paramètres optionnels : `country` (ISO 3166-1 alpha-3), `operationType` (`DEPOSIT`, `PAYOUT`, `REMITTANCE`, `PUSH_DEPOSIT`, `REFUND`, `NAME_LOOKUP`).

```json
GET https://api.sandbox.pawapay.io/v2/active-conf?country=BEN&operationType=DEPOSIT

{
  "companyName": "Merchant Inc.",
  "signatureConfiguration": { "signedRequestsOnly": true, "signedCallbacks": true },
  "countries": [
    {
      "country": "BEN",
      "displayName": { "en": "Benin", "fr": "Le Benin" },
      "prefix": "229",
      "flag": "https://cdn.com/ben_flag.png",
      "providers": [
        {
          "provider": "MTN_MOMO_BEN",
          "displayName": "MTN",
          "logo": "https://cdn.com/mtn_logo.png",
          "nameDisplayedToCustomer": "Merchant Inc.",
          "currencies": [
            {
              "currency": "XOF",
              "displayName": "CFA",
              "operationTypes": {
                "DEPOSIT": {
                  "authType": "PROVIDER_AUTH",
                  "pinPrompt": "AUTOMATIC",
                  "pinPromptRevivable": true,
                  "minAmount": "1",
                  "maxAmount": "1000",
                  "decimalsInAmount": "NONE",
                  "status": "OPERATIONAL",
                  "callbackUrl": "https://merchant.com/depositCallback"
                },
                "PAYOUT": { "minAmount": "1", "maxAmount": "1000", "decimalsInAmount": "NONE", "status": "DELAYED", "callbackUrl": "https://merchant.com/payoutCallback" },
                "REFUND": { "minAmount": "1", "maxAmount": "1000", "decimalsInAmount": "NONE", "status": "CLOSED", "callbackUrl": "https://merchant.com/refundCallback" },
                "REMITTANCE": { "minAmount": "1", "maxAmount": "1000", "decimalsInAmount": "NONE", "status": "OPERATIONAL", "callbackUrl": "https://merchant.com/remittanceCallback" }
              }
            }
          ]
        }
      ]
    }
  ]
}
```

**Champs clés :**
- `authType` : `PROVIDER_AUTH` (PIN standard), `PREAUTH` (OTP généré avant l'appel, ex. Orange Burkina Faso), `REDIRECT_AUTH` (redirection, ex. Wave Sénégal/Côte d'Ivoire).
- `pinPrompt` : `AUTOMATIC` (le prompt PIN s'affiche seul) ou `MANUAL` (le client doit composer un code USSD).
- `pinPromptRevivable` : le client peut relancer le prompt PIN en cas d'échec/timeout.
- `decimalsInAmount` : `NONE` ou `TWO_PLACES`.
- `status` par opération : `OPERATIONAL`, `DELAYED` (payouts mis en file, traités dès résolution), `CLOSED` (requêtes rejetées).

### `GET /v2/availability` — Provider Availability

Statut temps réel de chaque provider, groupé par pays, par type d'opération (`DEPOSIT`, `PAYOUT`, `REFUND`, `REMITTANCE`).

Paramètres optionnels : `country`, `operationType`.

```json
GET https://api.sandbox.pawapay.io/v2/availability?country=GHA

[
  {
    "country": "GHA",
    "providers": [
      { "provider": "VODAFONE_GHA", "operationTypes": [
        { "operationType": "DEPOSIT", "status": "OPERATIONAL" },
        { "operationType": "PAYOUT", "status": "DELAYED" },
        { "operationType": "REMITTANCE", "status": "OPERATIONAL" }
      ]},
      { "provider": "AIRTELTIGO_GHA", "operationTypes": [
        { "operationType": "DEPOSIT", "status": "CLOSED" }
      ]}
    ]
  }
]
```

Statuts : `OPERATIONAL`, `DELAYED`, `CLOSED` (voir section 12 "Active Configuration" pour la définition).

### `POST /v2/predict-provider` — Predict Provider

Valide/normalise un numéro de téléphone et prédit l'opérateur correspondant. Taux moyen d'erreur : **0,12 %** (jusqu'à ~6 % au Bénin, en raison de la portabilité des numéros).

```json
POST https://api.sandbox.pawapay.io/v2/predict-provider

{ "phoneNumber": "+260 763-456789" }
```

Le champ `phoneNumber` en entrée est nettoyé automatiquement (suppression du `+`, des espaces, des caractères non numériques).

**Réponse :**

```json
{ "country": "ZMB", "provider": "MTN_MOMO_ZMB", "phoneNumber": "260763456789" }
```

Utiliser le `phoneNumber` renvoyé (format sanitizé) pour initier le paiement.

### `GET /v2/public-keys` — Public Keys

Renvoie les clés publiques de PawaPay, utilisées pour vérifier la signature RFC-9421 des callbacks entrants.

---

## 13. Codes d'erreur communs

Présents sur la plupart des endpoints d'initiation (deposits, payouts, refunds, remittances, split-payments, checkouts, payment page) :

| HTTP | failureCode | Signification |
|---|---|---|
| 400 | `INVALID_INPUT` | Corps de requête illisible/malformé. |
| 400 | `MISSING_PARAMETER` | Paramètre requis manquant. |
| 400 | `INVALID_PARAMETER` | Valeur de paramètre invalide. |
| 400 | `UNSUPPORTED_PARAMETER` | Paramètre non reconnu inclus dans la requête. |
| 400 | `DUPLICATE_METADATA_FIELD` | Champ dupliqué dans `metadata`. |
| 401 | `NO_AUTHENTICATION` | Token API absent des headers. |
| 401 | `AUTHENTICATION_ERROR` | Token API invalide. |
| 401 | `HTTP_SIGNATURE_ERROR` | Signature de requête invalide (RFC-9421). |
| 403 | `AUTHORISATION_ERROR` | Token non autorisé pour cet endpoint. |
| 403 | `DEPOSITS_NOT_ALLOWED` / `PAYOUTS_NOT_ALLOWED` / `REMITTANCES_NOT_ALLOWED` / `REFUNDS_NOT_ALLOWED` / `SPLIT_PAYMENTS_NOT_ALLOWED` | Opération non activée pour ce provider sur votre compte. |
| 200 (payload) | `INVALID_PHONE_NUMBER` | Numéro invalide pour ce provider. |
| 200 (payload) | `INVALID_AMOUNT` | Décimales non supportées par ce provider. |
| 200 (payload) | `AMOUNT_OUT_OF_BOUNDS` | Montant hors limites min/max du provider. |
| 200 (payload) | `INVALID_CURRENCY` | Devise non supportée par ce provider. |
| 200 (payload) | `INVALID_PROVIDER` | Provider invalide pour cette requête. |
| 200 (payload) | `PROVIDER_TEMPORARILY_UNAVAILABLE` | Le provider n'accepte temporairement pas de paiements. |
| 500 | `UNKNOWN_ERROR` | Échec indéterminé — **ne pas considérer comme un échec certain**, vérifier via `Check status`. |

**Codes de statut final (callback / check status) additionnels :**
- `PAYMENT_NOT_APPROVED` — le client n'a pas validé le paiement (PIN).
- `UNSPECIFIED_FAILURE` — le provider a signalé un échec sans plus de précision.
- Liste complète : https://docs.pawapay.io/v2/docs/failure_codes

---

## 14. Aller en production

- Seuls diffèrent entre sandbox et production : l'**URL de base** et le **token API** — à stocker en configuration par environnement.
- Whitelister les IPs PawaPay si un filtrage IP est en place (voir section 3).
- Recommandé : implémenter un feature-flag pour tester le flux de bout en bout en production avant le lancement, avec un vrai téléphone/numéro/opérateur et un solde disponible.
- Contrairement au sandbox, l'authentification par PIN est réellement affichée au client en production (skip en sandbox) — prévoir un délai d'environ 1 à 20 secondes.
- Checklist recommandée :
  - [ ] Onboarding/KYC complété, token de production généré séparément.
  - [ ] `depositId`/`payoutId`/`refundId`/`remittanceId` générés en UUIDv4 et persistés **avant** l'appel API.
  - [ ] Statut vérifié dans la réponse d'initiation (pas seulement le code HTTP).
  - [ ] Callbacks configurés et endpoint idempotent (dédup par ID).
  - [ ] Statut final confirmé via callback ou `Check status` avant de livrer un bien/service.
  - [ ] Tous les `failureCode` gérés explicitement.
  - [ ] Testé avec les numéros sandbox des pays ciblés.
  - [ ] Signatures RFC-9421 envisagées si le niveau de sécurité du projet le justifie.

---

## Ressources complémentaires

- Documentation complète : https://docs.pawapay.io/v2/docs/welcome
- Référence API (OpenAPI, brute) : https://docs.pawapay.io/v2/api-reference/openapi_v2.yaml
- Guide détaillé Deposits (flux complet, cas PREAUTH/REDIRECT_AUTH, reconciliation) : https://docs.pawapay.io/v2/docs/deposits
- Guide Payouts : https://docs.pawapay.io/v2/docs/payouts
- Guide Refunds : https://docs.pawapay.io/v2/docs/refunds
- Guide Checkouts : https://docs.pawapay.io/v2/docs/checkouts
- Guide Payment Page : https://docs.pawapay.io/v2/docs/payment_page
- Providers & devises par pays : https://docs.pawapay.io/v2/docs/providers
- Numéros de test sandbox : https://docs.pawapay.io/v2/docs/test_numbers
- Codes d'échec complets : https://docs.pawapay.io/v2/docs/failure_codes
- Signatures RFC-9421 : https://docs.pawapay.io/v2/docs/signatures
- Guide "Going live" : https://docs.pawapay.io/v2/docs/going_live
- Statut plateforme : https://status.pawapay.io
- Collection Postman officielle : https://docs.pawapay.io/v2/docs/postman
