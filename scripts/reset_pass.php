<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';
$app = app();
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$u = App\Models\User::where('email', 'gabriela@gmail.com')->first();
$u->password = bcrypt('12345678');
$u->save();
echo "Password for " . $u->email . " reset to 12345678\n";
