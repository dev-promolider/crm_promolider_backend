<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$children = \Illuminate\Support\Facades\DB::table('classified')->where('user_above', '868')->get();
foreach($children as $child) {
    $u = \App\Models\User::find($child->user_id);
    echo "Child User ID: {$child->user_id}, Position: {$child->position}, Active: " . (($u->expiration_date > now()) ? 'Yes' : 'No') . ", Memb: " . (($u->expiration_membership_date > now()) ? 'Yes' : 'No') . PHP_EOL;
}
