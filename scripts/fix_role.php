<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(989);
if ($user) {
    $user->syncRoles(['Producer']);
    echo "Rol de {$user->name} actualizado a Producer exitosamente.\n";
} else {
    echo "User 989 not found.\n";
}
