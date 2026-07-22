<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gateway = app(\Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface::class);

$checkoutData = [
    'amount'      => 10.00,
    'description' => "Recompra OPC - 1 cuotas",
    'order_id'    => 'opc-test-' . time(),
    'currency'    => 'USD',
    'redirect_url' => 'http://localhost',
    'customer'    => [
        'name'         => 'Test',
        'last_name'    => 'User',
        'phone_number' => '123456789',
        'email'        => 'test@example.com',
    ],
    'send_email'   => false,
];

try {
    $result = $gateway->createCheckoutLink($checkoutData);
    print_r($result);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
