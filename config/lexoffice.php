<?php

return [
    'api_key' => env('LEXOFFICE_API_KEY'),

    'base_url' => env('LEXOFFICE_BASE_URL', 'https://api.lexoffice.io/v1'),

    'queue' => [
        'connection' => env('LEXOFFICE_QUEUE_CONNECTION', null),
        'name' => env('LEXOFFICE_QUEUE', 'lexoffice'),
    ],

    'retry' => [
        'times'              => 3,
        'backoff_base'       => 5,  // seconds; doubles per attempt: 5s → 10s → 20s
        'rate_limit_base'    => 30, // seconds for 429 responses: 30s → 60s → 120s
    ],

    'sync' => [
        'contacts' => true,
        'invoices' => true,
        'products' => true,
    ],

    'webhook' => [
        'secret' => env('LEXOFFICE_WEBHOOK_SECRET'),
        'path'   => env('LEXOFFICE_WEBHOOK_PATH', 'lexoffice/webhook'),
        'middleware' => ['api'],
    ],
];
