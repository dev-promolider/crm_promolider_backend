<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create(
    '/api/v1/registration/preregistro/webhook/openpay',
    'POST',
    ['type' => 'transaction', 'transaction' => ['id' => 'tryouz76bgds5bwhnpbf', 'status' => 'completed']]
);

$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . $response->getContent() . "\n";
