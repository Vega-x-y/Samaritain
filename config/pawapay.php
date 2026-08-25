<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PawaPay API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PawaPay Mobile Money payment gateway.
    | Supports deposits (collecting payments), payouts (disbursements),
    | and refunds across 20+ African countries.
    |
    */

    /*
    | API Base URL
    |
    | Sandbox: https://api.sandbox.pawapay.io
    | Production: https://api.pawapay.io
    |
    | IMPORTANT: These URLs are different between environments.
    | Never hardcode - always use environment variables.
    */
    'base_url' => env(
        'PAWAPAY_BASE_URL',
        env('PAWAPAY_ENV', 'sandbox') === 'production'
            ? env('PAWAPAY_LIVE_URL', 'https://api.pawapay.io')
            : env('PAWAPAY_SANDBOX_URL', 'https://api.sandbox.pawapay.io'),
    ),

    /*
    | API Token
    |
    | Generated from PawaPay Dashboard → System Configuration → API Tokens.
    | Sandbox and production tokens are different and not interchangeable.
    | NEVER expose this token client-side.
    */
    'token' => env('PAWAPAY_API_TOKEN'),

    /*
    | Verify Callback Signatures (RFC-9421)
    |
    | When enabled, incoming callbacks from PawaPay must have valid signatures.
    | Requires public key exchange via the PawaPay dashboard.
    | Recommended for production.
    */
    'verify_callback_signature' => env('PAWAPAY_CALLBACK_VERIFY_SIGNATURE', false),

    /*
    | Default Currency
    |
    | ISO 4217 three-character currency code.
    | Common codes: ZMW (Zambia), KES (Kenya), UGX (Uganda),
    | CDF (DRC), XAF (Cameroon), GHS (Ghana), NGN (Nigeria)
    */
    'default_currency' => env('PAWAPAY_CURRENCY', 'XAF'),

    /*
    | Available Providers
    |
    | List of Mobile Money providers to offer in your application.
    | These should match the providers configured in your PawaPay account.
    | Use the toolkit/active-configuration endpoint to get your exact list.
    |
    | Common provider codes:
    | - MTN_MOMO_COG (MTN Congo-Brazzaville)
    | - MTN_MOMO_COD (MTN DRC)
    | - AIRTEL_COG (Airtel Congo-Brazzaville)
    | - AIRTEL_COD (Airtel DRC)
    | - ORANGE_COD (Orange DRC)
    | - VODACOM_COD (Vodacom/M-Pesa DRC)
    */
    'providers' => [
        'MTN_MOMO_COG' => 'MTN Mobile Money',
        'AIRTEL_COG' => 'Airtel Money',
    ],

    /*
    | Callback URL
    |
    | The URL where PawaPay will send deposit/payout/refund callbacks.
    | Must be publicly accessible (HTTPS in production).
    | Configure in PawaPay Dashboard → System Configuration → Callback URLs.
    */
    'callback_url' => env('PAWAPAY_CALLBACK_URL'),

    /*
    | Payment Page Return URL
    |
    | The URL where users are redirected after completing payment
    | on PawaPay's hosted payment page.
    */
    'return_url' => env('PAWAPAY_RETURN_URL'),

    /*
    | Request Timeout (seconds)
    |
    | HTTP timeout for API requests to PawaPay.
    | PawaPay responds quickly, but allow enough time for network latency.
    */
    'timeout' => env('PAWAPAY_TIMEOUT', 30),

    /*
    | Retry Failed Requests
    |
    | Number of times to retry failed API requests (network errors only).
    | Does not retry rejected deposits/payouts.
    */
    'retry_times' => env('PAWAPAY_RETRY_TIMES', 2),

];
