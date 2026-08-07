<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get a user who is not admin
$distributor = App\Models\User::whereDoesntHave('roles', function($q) {
    $q->whereIn('name', ['admin', 'super-admin', 'producer']);
})->where("email", "!=", "dsanchez@promolider.org")->first();

if ($distributor) {
    $distributor->password = Illuminate\Support\Facades\Hash::make("12345678");
    $distributor->save();
    echo "\nFound Distributor: \n";
    echo "Email: " . $distributor->email . "\n";
    echo "Password: 12345678\n";
} else {
    echo "Could not find a distributor user.\n";
}
