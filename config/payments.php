<?php

return [
    'enabled' => env('PAYMENTS_ENABLED', false), // Set to false for v1
    'default_gateway' => env('PAYMENTS_DEFAULT_GATEWAY', 'thawani'),

    'gateways' => [
        'thawani' => [
            'enabled' => env('THAWANI_ENABLED', false),
            'api_key' => env('THAWANI_API_KEY'),
            'base_url' => env('THAWANI_BASE_URL', 'https://api.thawani.om'),
            'webhook_secret' => env('THAWANI_WEBHOOK_SECRET'),
            'publishable_key' => env('THAWANI_PUBLISHABLE_KEY'),
            'success_url' => env('THAWANI_SUCCESS_URL', 'https://example.com/payment/success'),
            'cancel_url' => env('THAWANI_CANCEL_URL', 'https://example.com/payment/cancel'),
        ],
    ],
];