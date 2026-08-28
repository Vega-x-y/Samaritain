<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'pawapay' => [
        'api_key' => env('PAWAPAY_API_KEY'),
        'api_url' => env('PAWAPAY_MODE', 'sandbox') === 'production'
            ? env('PAWAPAY_API_PRODUCTION_URL', 'https://api.pawapay.io/v2')
            : env('PAWAPAY_API_SANDBOX_URL', 'https://api.sandbox.pawapay.io/v2'),
        'currency' => env('PAWAPAY_CURRENCY', 'XAF'),
        'country' => env('PAWAPAY_COUNTRY', 'COG'),
        'dial_code' => env('PAWAPAY_DIAL_CODE', '242'),
        'fee_percent' => (int) env('PAWAPAY_FEE_PERCENT', 5),
        'timeout' => (int) env('PAWAPAY_TIMEOUT', 30),
        'retry_times' => (int) env('PAWAPAY_RETRY_TIMES', 2),
    ],
];
