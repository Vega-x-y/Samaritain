# Providers PawaPay et numéros de test sandbox

Catalogue complet et à jour, toujours vérifiable via `GET /v2/active-conf` (ce qui est réellement activé sur ton compte) ou https://docs.pawapay.io/v2/docs/providers.

## Codes provider principaux (non exhaustif — se référer à active-configuration)

| Pays | Opérateur | Code provider |
|---|---|---|
| Bénin | MTN | `MTN_MOMO_BEN` |
| Bénin | Moov | `MOOV_BEN` |
| Burkina Faso | Moov | `MOOV_BFA` |
| Burkina Faso | Orange | `ORANGE_BFA` |
| Cameroun | MTN | `MTN_MOMO_CMR` |
| Cameroun | Orange | `ORANGE_CMR` |
| Côte d'Ivoire | MTN | `MTN_MOMO_CIV` |
| Côte d'Ivoire | Orange | `ORANGE_CIV` |
| RD Congo | Vodacom M-Pesa | `VODACOM_MPESA_COD` |
| RD Congo | Airtel | `AIRTEL_COD` |
| RD Congo | Orange | `ORANGE_COD` |
| Éthiopie | Safaricom M-Pesa | `MPESA_ETH` |
| Gabon | Airtel | `AIRTEL_GAB` |
| Ghana | MTN | `MTN_MOMO_GHA` |
| Ghana | AT (AirtelTigo) | `AIRTELTIGO_GHA` |
| Ghana | Vodafone | `VODAFONE_GHA` |
| Kenya | M-Pesa | `MPESA_KEN` |
| Lesotho | M-Pesa | `MPESA_LSO` |
| Malawi | Airtel | `AIRTEL_MWI` |
| Malawi | TNM | `TNM_MWI` |
| Mozambique | Movitel | `MOVITEL_MOZ` |
| Nigeria | Airtel | `AIRTEL_NGA` |
| Nigeria | MTN | `MTN_MOMO_NGA` |
| Congo-Brazzaville | Airtel | `AIRTEL_COG` |
| Congo-Brazzaville | MTN | `MTN_MOMO_COG` |
| Rwanda | Airtel | `AIRTEL_RWA` |
| Rwanda | MTN | `MTN_MOMO_RWA` |
| Sénégal | Free | `FREE_SEN` |
| Sénégal | Orange | `ORANGE_SEN` |
| Sierra Leone | Orange | `ORANGE_SLE` |
| Tanzanie | Airtel | `AIRTEL_TZA` |
| Tanzanie | Vodacom | `VODACOM_TZA` |
| Tanzanie | Tigo | `TIGO_TZA` |
| Tanzanie | Halotel | `HALOTEL_TZA` |
| Ouganda | Airtel | `AIRTEL_OAPI_UGA` |
| Ouganda | MTN | `MTN_MOMO_UGA` |
| Zambie | Airtel | `AIRTEL_OAPI_ZMB` |
| Zambie | MTN | `MTN_MOMO_ZMB` |
| Zambie | Zamtel | `ZAMTEL_ZMB` |

## Numéros de test sandbox (MSISDN)

Utilisables **uniquement en sandbox**. Le comportement dépend entièrement du numéro utilisé — pas de flag séparé. En sandbox le client ne valide pas par PIN, le résultat arrive plus vite qu'en production.

### Cameroun

**MTN (`MTN_MOMO_CMR`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 237653456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 237653456029 | FAILED | PAYER_NOT_FOUND |
| Dépôt | 237653456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 237653456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 237653456129 | SUBMITTED | — |
| Dépôt | 237653456789 | COMPLETED | — |
| Payout | 237653456089 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 237653456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 237653456129 | SUBMITTED | — |
| Payout | 237653456789 | COMPLETED | — |

**Orange (`ORANGE_CMR`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 237693456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 237693456029 | FAILED | PAYER_NOT_FOUND |
| Dépôt | 237693456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 237693456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 237693456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 237693456129 | SUBMITTED | — |
| Dépôt | 237693456789 | COMPLETED | — |
| Payout | 237693456099 | FAILED | WALLET_LIMIT_REACHED |
| Payout | 237693456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 237693456129 | SUBMITTED | — |
| Payout | 237693456789 | COMPLETED | — |

### Zambie

**MTN (`MTN_MOMO_ZMB`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 260763456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 260763456029 | FAILED | PAYER_NOT_FOUND |
| Dépôt | 260763456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 260763456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 260763456129 | SUBMITTED | — |
| Dépôt | 260763456789 | COMPLETED | — |
| Payout | 260763456079 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 260763456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 260763456129 | SUBMITTED | — |
| Payout | 260763456789 | COMPLETED | — |

**Airtel (`AIRTEL_OAPI_ZMB`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 260973456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 260973456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 260973456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 260973456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 260973456129 | SUBMITTED | — |
| Dépôt | 260973456789 | COMPLETED | — |
| Payout | 260973456089 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 260973456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 260973456129 | SUBMITTED | — |
| Payout | 260973456789 | COMPLETED | — |

**Zamtel (`ZAMTEL_ZMB`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 260953456704 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 260953456712 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 260953456789 | SUBMITTED | — |
| Dépôt | 260953456700 | COMPLETED | — |

### Kenya

**M-Pesa (`MPESA_KEN`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 254703456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 254703456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 254703456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 254703456059 | FAILED | TRANSACTION_ALREADY_IN_PROCESS |
| Dépôt | 254703456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 254703456129 | SUBMITTED | — |
| Dépôt | 254703456789 | COMPLETED | — |
| Payout | 254703456089 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 254703456099 | FAILED | WALLET_LIMIT_REACHED |
| Payout | 254703456109 | FAILED | RECIPIENT_LIMIT_REACHED |
| Payout | 254703456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 254703456129 | SUBMITTED | — |
| Payout | 254703456789 | COMPLETED | — |

### Ouganda

**MTN (`MTN_MOMO_UGA`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 256783456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 256783456029 | FAILED | PAYER_NOT_FOUND |
| Dépôt | 256783456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 256783456129 | SUBMITTED | — |
| Dépôt | 256783456789 | COMPLETED | — |
| Payout | 256783456089 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 256783456099 | FAILED | WALLET_LIMIT_REACHED |
| Payout | 256783456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 256783456789 | COMPLETED | — |

**Airtel (`AIRTEL_OAPI_UGA`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 256753456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 256753456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 256753456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 256753456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 256753456129 | SUBMITTED | — |
| Dépôt | 256753456789 | COMPLETED | — |
| Payout | 256753456089 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 256753456099 | FAILED | WALLET_LIMIT_REACHED |
| Payout | 256753456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 256753456129 | SUBMITTED | — |
| Payout | 256753456789 | COMPLETED | — |

### RD Congo

**Vodacom M-Pesa (`VODACOM_MPESA_COD`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 243813456019 | FAILED | PAYER_LIMIT_REACHED |
| Dépôt | 243813456029 | FAILED | PAYER_NOT_FOUND |
| Dépôt | 243813456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 243813456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 243813456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 243813456129 | SUBMITTED | — |
| Dépôt | 243813456789 | COMPLETED | — |
| Payout | 243813456089 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 243813456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 243813456129 | SUBMITTED | — |
| Payout | 243813456789 | COMPLETED | — |

**Airtel (`AIRTEL_COD`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 243973456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 243973456129 | SUBMITTED | — |
| Dépôt | 243973456789 | COMPLETED | — |
| Payout | 243973456089 | FAILED | RECIPIENT_NOT_FOUND |
| Payout | 243973456119 | FAILED | UNSPECIFIED_FAILURE |
| Payout | 243973456129 | SUBMITTED | — |
| Payout | 243973456789 | COMPLETED | — |

**Orange (`ORANGE_COD`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 243893456029 | FAILED | PAYER_NOT_FOUND |
| Dépôt | 243893456039 | FAILED | PAYMENT_NOT_APPROVED |
| Dépôt | 243893456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 243893456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 243893456129 | SUBMITTED | — |
| Dépôt | 243893456789 | COMPLETED | — |

### Sénégal

**Orange (`ORANGE_SEN`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 221773456029 | FAILED | PAYER_NOT_FOUND |
| Dépôt | 221773456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 221773456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 221773456129 | SUBMITTED | — |
| Dépôt | 221773456789 | COMPLETED | — |

**Free (`FREE_SEN`)**
| Opération | MSISDN | Statut | failureCode |
|---|---|---|---|
| Dépôt | 221763456049 | FAILED | INSUFFICIENT_BALANCE |
| Dépôt | 221763456069 | FAILED | UNSPECIFIED_FAILURE |
| Dépôt | 221763456129 | SUBMITTED | — |
| Dépôt | 221763456789 | COMPLETED | — |

### Autres pays (Bénin, Burkina Faso, Côte d'Ivoire, Éthiopie, Gabon, Ghana, Lesotho, Malawi, Mozambique, Nigeria, Congo-Brazzaville, Rwanda, Sierra Leone, Tanzanie)

Chaque pays/opérateur suit le même principe : un numéro se terminant par un suffixe donné produit systématiquement `COMPLETED`, `FAILED` (avec `failureCode` précis), ou `SUBMITTED`. Le pattern varie par opérateur — **ne pas deviner**, toujours vérifier le numéro exact sur https://docs.pawapay.io/v2/docs/test_numbers pour le pays/opérateur ciblé avant d'écrire les tests.

## Codes d'échec (failureCode) les plus courants

| Code | Signification |
|---|---|
| `PAYER_NOT_FOUND` / `RECIPIENT_NOT_FOUND` | Numéro non enregistré chez l'opérateur |
| `PAYER_LIMIT_REACHED` / `RECIPIENT_LIMIT_REACHED` / `WALLET_LIMIT_REACHED` | Plafond de transaction/wallet atteint |
| `PAYMENT_NOT_APPROVED` | Client a refusé/annulé sur son téléphone |
| `INSUFFICIENT_BALANCE` | Solde insuffisant |
| `TRANSACTION_ALREADY_IN_PROCESS` | Une transaction est déjà en cours pour ce payeur |
| `PROVIDER_TEMPORARILY_UNAVAILABLE` | Panne/maintenance côté opérateur — voir https://status.pawapay.cloud/ |
| `UNSPECIFIED_FAILURE` | Échec générique côté opérateur |
| `AMOUNT_OUT_OF_BOUNDS` | Montant hors limites min/max du provider |
| `INVALID_PHONE_NUMBER` | Format de numéro invalide pour ce provider |

Liste complète : https://docs.pawapay.io/v2/docs/failure_codes