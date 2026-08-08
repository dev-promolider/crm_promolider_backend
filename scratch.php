<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \DB::table('users')->orderBy('id', 'desc')->take(3)->get(['id', 'name', 'username', 'email', 'request', 'created_at']);
echo "USERS:\n";
echo json_encode($users, JSON_PRETTY_PRINT) . "\n";

$classified = \DB::table('classified')->orderBy('id', 'desc')->take(5)->get();
echo "\nCLASSIFIED:\n";
echo json_encode($classified, JSON_PRETTY_PRINT) . "\n";
