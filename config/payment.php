<?php

return [
    'default' => env('DEFAULT_PAYMENT_GATEWAY', 'rintisan'),

    'gateways' => [
        'rintisan' => [
            'enabled' => env('RINTISAN_ENABLED', true),
            'base_url' => env('RINTISAN_API_BASE_URL'),
            'client_id' => env('RINTISAN_CLIENT_ID'),
            'api_key' => env('RINTISAN_API_KEY'),
            'callback_token' => env('RINTISAN_CALLBACK_TOKEN'),
            'private_key_path' => env('RINTISAN_UT_PRIVATE_KEY_PATH'),
            'public_key_path' => env('RINTISAN_PUBLIC_KEY_PATH'),
        ],

        'paypro' => [
            'enabled' => env('PAYPRO_ENABLED', false),
            'base_url' => env('PAYPRO_API_BASE_URL'),
            'merchant_no' => env('PAYPRO_MERCHANT_NO'),
            'app_secret' => env('PAYPRO_APP_SECRET'),
        ],

        'e2pay' => [
            'enabled' => env('E2PAY_ENABLED', false),
            'base_url' => env('E2PAY_API_BASE_URL'),
            'query_base_url' => env('E2PAY_API_QUERY_BASE_URL'),
            'merchant_id' => env('E2PAY_API_MERCHANT_ID'),
            'verify_key' => env('E2PAY_API_VERIFY_KEY'),
        ],

        'durianpay' => [
            'enabled' => env('DURIANPAY_ENABLED', false),
            'base_url' => env('DURIANPAY_LEGACY_BASE_URL'),
            'api_key' => env('DURIANPAY_LEGACY_API_KEY'),
        ],

        'nusandana' => [
            'enabled' => env('NUSANDANA_ENABLED', false),
            'base_url' => env('NUSANDANA_API_BASE_URL'),
            'merchant_no' => env('NUSANDANA_MERCHANT_NO'),
            'signature_key' => env('NUSANDANA_SIGNATURE_KEY'),
            'is_sandbox' => env('NUSANDANA_IS_SANDBOX', false),
        ],
    ],

    'disbursement' => [
        'max_amount' => env('MAX_AMOUNT_PER_DISBURSEMENT', 250000000),
        'fee' => env('INTERNAL_DISBURSEMENT_FEE', 4000),
        'internal_account' => [
            'number' => env('INTERNAL_ACCOUNT_NUMBER'),
            'bank' => env('INTERNAL_BANK_NAME'),
            'name' => env('INTERNAL_ACCOUNT_NAME'),
        ],
    ],

    'monitoring' => [
        'watchlist_merchants' => explode(',', env('WATCHLIST_MERCHANT_IDS', '')),
        'watchlist_cache_ttl' => env('WATCHLIST_ORDER_CACHE_TTL', 300),
    ],
];