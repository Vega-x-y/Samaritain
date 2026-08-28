<?php

return [
    'api_key' => env('PAWAPAY_API_KEY'),
    'api_url' => env('PAWAPAY_MODE', 'sandbox') === 'production'
        ? env('PAWAPAY_API_PRODUCTION_URL', 'https://api.pawapay.io/v2')
        : env('PAWAPAY_API_SANDBOX_URL', 'https://api.sandbox.pawapay.io/v2'),
    'mode' => env('PAWAPAY_MODE', 'sandbox'),
    'currency' => env('PAWAPAY_CURRENCY', 'XAF'),
    'country' => env('PAWAPAY_COUNTRY', 'COG'),
    'dial_code' => env('PAWAPAY_DIAL_CODE', '242'),
    'fee_percent' => (int) env('PAWAPAY_FEE_PERCENT', 5),
    'timeout' => (int) env('PAWAPAY_TIMEOUT', 30),
    'retry_times' => (int) env('PAWAPAY_RETRY_TIMES', 2),
];
