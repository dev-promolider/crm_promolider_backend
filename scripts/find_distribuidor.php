<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$app = app();
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$users = App\Models\User::role('Distributor')->inRandomOrder()->take(3)->get();
foreach($users as $u) {
    echo 'Name: ' . $u->name . ' | Email: ' . $u->email . " | Password: password (default)\n";
}
