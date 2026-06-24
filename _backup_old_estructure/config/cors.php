<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie','chats','chats/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://vcr.promolider.info',
        'http://localhost:8081',
        'http://localhost:5173',
        'http://localhost:5174',
        'https://crm.promolider.info',
        'https://agente.picklechatbot.promolider.org'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
    #cambiar a true si es necesario
];