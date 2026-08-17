<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$courses = \App\Models\Infoproduct\Infoproduct::where('status', 2)
    ->select('id', 'title', 'price', 'product_type_id', 'id_categories')
    ->get();

$free = $courses->where('price', 0)->take(5)->values();
$paid = $courses->where('price', '>', 0)->take(5)->values();

echo json_encode(['free' => $free, 'paid' => $paid], JSON_PRETTY_PRINT);
