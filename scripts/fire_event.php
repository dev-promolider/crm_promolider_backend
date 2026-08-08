<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fire the event for Luis Angel (989)
broadcast(new App\Events\NewNotificationEvent([
    'id' => 9999, // Fake ID
    'title' => '¡Felicidades, ahora eres Creador de Cursos!',
    'body' => 'Tu solicitud ha sido aprobada. Ahora tienes acceso a nuevas herramientas de monetización.',
    'type' => 'producer_approved',
    'id_receiver' => 989
]));

echo "Evento disparado para user 989\n";
