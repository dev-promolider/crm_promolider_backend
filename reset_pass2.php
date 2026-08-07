<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(897);
if ($user) {
    $user->password = Hash::make('12345678');
    $user->save();
    echo "Password for {$user->name} reset successfully.\n";
}
