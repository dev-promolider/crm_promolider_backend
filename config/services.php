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

    'n8n' => [
        'preregistro_webhook' => env('N8N_URL', 'https://ia.promolider.org/webhook-test/registro-promolider'),
        'radar_webhook'       => env('N8N_RADAR_URL', 'https://ia.promolider.org/webhook-test/pre_pago'),
    ],

    'openpay' => [
        'id'              => env('OPENPAY_ID'),
        'sk'              => env('OPENPAY_SK_DECODED'),
        'sk_encoded'      => env('OPENPAY_SK_ENCODED'),
        'production_mode' => env('OPENPAY_PRODUCTION_MODE', false),
    ],

];
