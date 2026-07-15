<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ids = \Illuminate\Support\Facades\DB::table('users')->pluck('id');
foreach($ids as $id) {
    try {
        \Illuminate\Support\Facades\DB::table('binary_cut_histories')->insert([
            'user_id' => $id,
            'left_points' => 500,
            'right_points' => 300,
            'transferred_amount' => 30.00,
            'rank_id' => 1,
            'batch' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    } catch (\Exception $e) {
        // ignore duplicates or foreign key errors
    }
}
echo "Done";
