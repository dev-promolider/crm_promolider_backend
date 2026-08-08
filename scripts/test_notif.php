<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = \App\Models\User::first();
if (!$admin) {
    echo "No admin found\n";
    exit;
}

$notifId = \Illuminate\Support\Facades\DB::table('notifications')->insertGetId([
    'id_receiver' => $admin->id, 
    'title' => '¡Hola Admin!', 
    'body' => 'Esta es una notificación de prueba en tiempo real', 
    'type' => 1, 
    'seen' => 0, 
    'created_at' => now(), 
    'updated_at' => now()
]);

event(new \App\Events\NewNotificationEvent([
    'id' => $notifId, 
    'id_receiver' => $admin->id, 
    'title' => '¡Hola Admin!', 
    'body' => 'Esta es una notificación de prueba en tiempo real', 
    'type' => 'info', 
    'photo' => null
]));

echo "Success! user_id: {$admin->id}, notif_id: {$notifId}\n";
