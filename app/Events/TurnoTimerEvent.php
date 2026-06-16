<?php

namespace App\Events;

use App\Models\Dinamica;
use App\Models\DinamicaRegistro;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class TurnoTimerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dinamicaSlug;
    public $turno;
    public $startedAt;
    public $expiresAt;
    public $duration;

    public function __construct(
        Dinamica $dinamica,
        DinamicaRegistro $registro,
        Carbon $expiresAt,
        int $duration
    ) {
        $this->dinamicaSlug = $dinamica->slug;
        $this->turno = [
            'id' => $registro->id,
            'turno' => $registro->turno,
            'nombre' => $registro->nombre,
            'apellido' => $registro->apellido,
        ];
        $this->startedAt = $registro->turno_inicio
            ? $registro->turno_inicio->toIso8601String()
            : null;
        $this->expiresAt = $expiresAt->toIso8601String();
        $this->duration = $duration;
    }

    public function broadcastOn()
    {
        return new Channel('public-ruleta');
    }
}
