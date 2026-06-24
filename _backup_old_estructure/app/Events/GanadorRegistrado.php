<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GanadorRegistrado implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $dinamicaId;
    public $ganador;
    public $premio;

    public function __construct($dinamicaId, $ganador, $premio)
    {
        $this->dinamicaId = $dinamicaId;
        $this->ganador = $ganador;
        $this->premio = $premio;
    }

    public function broadcastOn()
    {
        return new Channel('dinamica.' . $this->dinamicaId);
    }
}
