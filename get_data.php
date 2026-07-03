<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$roles = DB::table('roles')->where('name', '!=', 'Admin')->get();
$accountTypes = DB::table('account_type')->where('status', '1')->get();

echo "ROLES:\n";
echo json_encode($roles, JSON_PRETTY_PRINT);
echo "\n\nACCOUNT TYPES:\n";
echo json_encode($accountTypes, JSON_PRETTY_PRINT);
