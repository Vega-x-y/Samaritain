---
name: pawapay-integration
description: Intègre le moyen de paiement PawaPay (pawapay.io) — paiements Mobile Money (dépôts, retraits/payouts, remboursements) dans 20+ pays africains via une API unique et connexions directes aux opérateurs (MTN, Airtel, Orange, M-Pesa, Vodacom, Moov, Zamtel, etc.). À utiliser dès que l'utilisateur mentionne PawaPay, pawapay.io, ou veut ajouter un paiement Mobile Money pour l'Afrique subsaharienne (Zambie, Kenya, Ouganda, RD Congo, Cameroun, Ghana, Nigeria, Rwanda, Sénégal, Tanzanie, etc.) à son site, son app ou son backend. Couvre l'authentification par bearer token, l'initiation de dépôt (deposit) avec vérification de statut asynchrone, les retraits vers un client (payout), les remboursements (refund), la page de paiement hébergée (Payment Page), les callbacks/webhooks et leur signature RFC-9421, le mode sandbox et les numéros de test par pays/opérateur. Utiliser aussi pour déboguer une intégration PawaPay existante ou migrer de l'API v1 vers v2.
---

# Intégration PawaPay

Guide pour intégrer l'API PawaPay (Mobile Money) dans une application. **Toujours coder côté serveur** — le token d'API ne doit jamais être exposé côté client (frontend, app mobile).

## Vue d'ensemble

PawaPay est une **API asynchrone** : chaque appel d'initiation (dépôt, retrait, remboursement) retourne immédiatement un statut d'acceptation (`ACCEPTED` / `REJECTED` / `DUPLICATE_IGNORED`), pas le résultat final. Le statut final arrive plus tard via **callback** (recommandé) ou en interrogeant l'endpoint de statut (polling).

Base URLs :

| Environnement | API | Dashboard |
|---|---|---|
| Sandbox | `https://api.sandbox.pawapay.io` | `https://dashboard.sandbox.pawapay.io` |
| Production | `https://api.pawapay.io` | `https://dashboard.pawapay.io` |

Ces URLs sont différentes entre sandbox et production — à stocker en configuration par environnement, jamais en dur.

## Étape 1 — Compte et token API

1. Créer un compte sur https://www.pawapay.io/plans — accès immédiat au sandbox (isolé, aucun argent réel).
2. Générer un token depuis le dashboard : System Configuration → API Tokens.
3. L'accès production n'est débloqué qu'après onboarding complet sur le compte sandbox (KYC/compliance).

**Toujours** :
- Stocker le token en variable d'environnement (`PAWAPAY_API_TOKEN`), jamais en dur.
- Utiliser un token sandbox en dev, un token production distinct en prod (à régénérer, ils ne sont pas interchangeables).
- Ne jamais exposer le token côté client.

Authentification : header `Authorization: Bearer <token>` sur chaque appel.

## Étape 2 — Dépôts (encaisser un paiement client)

`POST /v2/deposits`

```javascript
const res = await fetch("https://api.sandbox.pawapay.io/v2/deposits", {
  method: "POST",
  headers: {
    "Authorization": `Bearer ${process.env.PAWAPAY_API_TOKEN}`,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    depositId: crypto.randomUUID(),        // UUIDv4 généré par toi, obligatoire — clé d'idempotence
    payer: {
      type: "MMO",
      accountDetails: {
        phoneNumber: "260763456789",        // MSISDN sans '+', code pays obligatoire, pas de zéro initial
        provider: "MTN_MOMO_ZMB",            // voir catalogue des providers
      },
    },
    amount: "15",                            // string, pas de zéros de tête (sauf valeurs < 1)
    currency: "ZMW",                         // ISO 4217, doit être supportée par le provider
    clientReferenceId: "INV-123456",         // optionnel : ta référence interne (facture, commande...)
    customerMessage: "Note de 4 a 22 car",   // optionnel, alphanumérique, visible par le client selon l'opérateur
    metadata: [{ orderId: "ORD-123456789" }],// optionnel, jusqu'à 10 champs
  }),
});
const data = await res.json();
// 200 → { depositId: "...", status: "ACCEPTED" | "REJECTED" | "DUPLICATE_IGNORED", created: "...", failureReason?: {...} }
```

Points clés :
- **`depositId`** : UUIDv4 que TU génères et stockes avant l'appel. C'est la clé d'idempotence — rejouer la requête avec le même `depositId` ne crée pas de doublon (`DUPLICATE_IGNORED`).
- **Toujours vérifier `status` dans la réponse**, même sur un HTTP 200 : une requête bien formée peut être `REJECTED` (ex. `AMOUNT_OUT_OF_BOUNDS`, `PROVIDER_TEMPORARILY_UNAVAILABLE`, `INVALID_PHONE_NUMBER`).
- `ACCEPTED` ne veut pas dire payé — le client doit encore valider par PIN sur son téléphone. Attendre le statut final.
- Codes d'échec possibles : `NO_AUTHENTICATION`, `AUTHENTICATION_ERROR`, `AUTHORISATION_ERROR`, `INVALID_INPUT`, `MISSING_PARAMETER`, `INVALID_PARAMETER`, `INVALID_PHONE_NUMBER`, `INVALID_AMOUNT`, `AMOUNT_OUT_OF_BOUNDS`, `INVALID_CURRENCY`, `INVALID_PROVIDER`, `PROVIDER_TEMPORARILY_UNAVAILABLE`, `DEPOSITS_NOT_ALLOWED`, `UNKNOWN_ERROR`.

### Suivre le statut final

`GET /v2/deposits/{depositId}`

```javascript
const res = await fetch(`https://api.sandbox.pawapay.io/v2/deposits/${depositId}`, {
  headers: { "Authorization": `Bearer ${process.env.PAWAPAY_API_TOKEN}` },
});
const { status, data } = await res.json();
// status: "FOUND" | "NOT_FOUND"
// data.status: "COMPLETED" | "FAILED" | "SUBMITTED" (en cours) | ...
```

Utiliser en complément des callbacks (fallback si pas configurés, ou pour reconciliation ponctuelle).

## Étape 3 — Retraits / Payouts (envoyer de l'argent à un client)

`POST /v2/payouts` — même logique, même authentification.

```javascript
const res = await fetch("https://api.sandbox.pawapay.io/v2/payouts", {
  method: "POST",
  headers: {
    "Authorization": `Bearer ${process.env.PAWAPAY_API_TOKEN}`,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    payoutId: crypto.randomUUID(),
    amount: "15",
    currency: "ZMW",
    recipient: {
      type: "MMO",
      accountDetails: {
        phoneNumber: "260763456789",
        provider: "MTN_MOMO_ZMB",
      },
    },
  }),
});
```

- Statuts d'initiation : `ACCEPTED`, `ENQUEUED` (mis en file, ex. hors plafond instantané), `REJECTED`, `DUPLICATE_IGNORED`.
- `GET /v2/payouts/{payoutId}` pour le statut final, ou callback.
- Un payout `ENQUEUED` peut être annulé via `POST /v2/payouts/{payoutId}/cancel` tant qu'il n'est pas traité.
- Bulk payouts disponibles via `POST /v2/payouts/bulk` pour envoyer plusieurs paiements en un appel.

## Étape 4 — Remboursements (refunds)

`POST /v2/refunds` — rembourse un dépôt déjà `COMPLETED`.

```javascript
const res = await fetch("https://api.sandbox.pawapay.io/v2/refunds", {
  method: "POST",
  headers: {
    "Authorization": `Bearer ${process.env.PAWAPAY_API_TOKEN}`,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    refundId: crypto.randomUUID(),
    depositId: "f4401bd2-1568-4140-bf2d-eb77d2b2b639", // le dépôt à rembourser
    amount: "15", // optionnel : montant partiel, sinon remboursement total
  }),
});
```

Codes de rejet spécifiques : `DEPOSIT_NOT_FOUND`, `DEPOSIT_NOT_COMPLETED` (on ne peut rembourser qu'un dépôt terminé avec succès).

## Étape 5 — Page de paiement hébergée (sans gérer le formulaire toi-même)

`POST /v2/deposits/payment-page` — PawaPay héberge la page où le client choisit son opérateur et son numéro.

```javascript
const res = await fetch("https://api.sandbox.pawapay.io/v2/deposits/payment-page", {
  method: "POST",
  headers: {
    "Authorization": `Bearer ${process.env.PAWAPAY_API_TOKEN}`,
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    depositId: crypto.randomUUID(),
    returnUrl: "https://monsite.com/return",
    amount: "15",
    currency: "ZMW",
  }),
});
const { redirectUrl } = await res.json();
// Rediriger le client vers redirectUrl
```

- Le dépôt n'est réellement initié que quand le client clique "Pay" sur la page — s'il abandonne, le dépôt reste `NOT_FOUND` puis doit être considéré `FAILED` après 15 minutes (session expirée).
- Sur `returnUrl`, **toujours confirmer via callback ou `GET /v2/deposits/{depositId}`** avant de valider la commande — ne jamais se fier au seul retour de redirection.

## Étape 6 — Callbacks (recommandé — source de vérité)

Configurer l'URL de callback depuis le dashboard : System Configuration → Callback URLs.

Exemple de payload reçu pour un dépôt terminé :

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

- Répondre vite (200 OK) pour acquitter — traiter le reste en async si besoin.
- **Idempotence obligatoire** : le même callback peut arriver plusieurs fois, dédupliquer via `depositId`/`payoutId`/`refundId`.
- Si aucun callback configuré, se rabattre entièrement sur le polling (`GET /v2/{deposits|payouts|refunds}/{id}`).
- Endpoints `resend-callback` disponibles si un callback a été manqué : `POST /v2/deposits/{depositId}/resend-callback` (idem payouts/refunds).

### Vérification de signature (sécurité renforcée, optionnelle mais recommandée)

PawaPay signe les requêtes financières sortantes (callbacks) et peut exiger que TES requêtes entrantes soient signées, selon **RFC-9421 (HTTP Message Signatures)** :

- Fournir ta clé publique dans le dashboard et activer "Only accept signed requests" pour que PawaPay n'accepte que des requêtes signées par toi (protège même si le token API leak).
- Vérifier le header `Content-Digest` (hash du corps) et `Signature`/`Signature-Input` sur les callbacks reçus pour t'assurer qu'ils viennent bien de PawaPay et n'ont pas été altérés.
- Récupérer les clés publiques de PawaPay via `GET /v2/public-keys` pour vérifier leurs signatures.
- Détail complet : https://docs.pawapay.io/v2/docs/signatures

## Étape 7 — Providers et numéros de téléphone

- Utiliser `GET /v2/toolkit/predict-provider?phoneNumber=...` pour valider/normaliser un numéro et prédire l'opérateur avant d'initier un paiement, plutôt que de deviner.
- `GET /v2/toolkit/active-configuration` liste les providers réellement activés sur ton compte, avec devises supportées et limites de transaction (source de vérité — plus fiable qu'une liste statique).
- `GET /v2/toolkit/availability` donne la disponibilité en temps réel par provider (pannes, maintenance).
- Format du numéro : uniquement des chiffres, sans `+` ni espace, sans zéro initial, code pays obligatoire.

Voir `references/providers-and-test-numbers.md` pour le catalogue des principaux codes provider (MTN, Airtel, Orange, M-Pesa, Vodacom, Moov, Zamtel...) par pays.

## Étape 8 — Mode sandbox et tests

Le compte sandbox est accessible immédiatement à l'inscription, sans KYC, avec accès à tous les providers. **Le comportement dépend du numéro de test (MSISDN) utilisé**, pas d'un paramètre séparé — chaque pays/opérateur a son propre jeu de numéros donnant `COMPLETED`, `FAILED` (avec `failureCode` précis), ou `SUBMITTED`.

En sandbox, le flux est plus rapide qu'en production : le client ne valide pas explicitement par PIN.

Voir `references/providers-and-test-numbers.md` pour les numéros de test complets par pays et opérateur — **toujours vérifier le numéro exact pour le pays/opérateur ciblé** plutôt que de deviner un pattern, les suffixes varient d'un opérateur à l'autre.

## Checklist avant la production

- [ ] Onboarding/KYC complété sur le dashboard, token de production généré séparément.
- [ ] Token stocké en variable d'environnement / secrets manager, jamais côté client.
- [ ] `depositId`/`payoutId`/`refundId` générés en UUIDv4 et persistés AVANT l'appel API (idempotence).
- [ ] Statut vérifié dans la réponse d'initiation, pas seulement le code HTTP.
- [ ] Callback configuré et endpoint idempotent (dédup par ID).
- [ ] Statut final confirmé via callback ou `GET .../{id}` avant de livrer un bien/service — jamais sur la seule redirection de la Payment Page.
- [ ] Tous les codes d'échec (`failureCode`) gérés explicitement, pas juste succès/échec binaire.
- [ ] Testé avec les numéros sandbox du/des pays réellement ciblés (succès, échec typé, en attente).
- [ ] Envisagé les signatures RFC-9421 si le niveau de sécurité du projet le justifie.

## Ressources additionnelles

- `references/providers-and-test-numbers.md` — codes provider principaux et numéros de test par pays.
- Documentation complète : https://docs.pawapay.io/v2/docs/welcome
- Référence API complète (OpenAPI) : https://docs.pawapay.io/v2/api-reference
- Guide "Going live" : https://docs.pawapay.io/v2/docs/going_live
- Statut de la plateforme (pannes providers) : https://status.pawapay.cloud/
- Collection Postman officielle pour tester rapidement : https://docs.pawapay.io/v2/docs/postman