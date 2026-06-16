
<?php

return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET')
    ],

    'ruleta' => [
        'turn_duration' => (int) env('RULETA_TURNO_SEGUNDOS', 90),
    ],

    'openpay' => [
        'id'              => env('OPENPAY_ID'),
        'sk'              => env('OPENPAY_SK_DECODED'),
        'sk_encoded'      => env('OPENPAY_SK_ENCODED'),
        'webhook_user'    => env('OPENPAY_WEBHOOK_USER'),
        'webhook_pass'    => env('OPENPAY_WEBHOOK_PASS'),
    ],

    'n8n' => [
        'webhook_user' => env('N8N_WEBHOOK_USER'),
        'webhook_pass' => env('N8N_WEBHOOK_PASS'),
    ],

];
