<?php

namespace Promolider\Infrastructure\Marketing\In\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TurnoTimerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $dinamicaSlug;
    public ?array $turno;
    public ?string $startedAt;
    public ?string $expiresAt;
    public int $duration;

    public function __construct(
        string $dinamicaSlug,
        ?array $turno,
        ?string $startedAt,
        ?string $expiresAt,
        int $duration
    ) {
        $this->dinamicaSlug = $dinamicaSlug;
        $this->turno = $turno;
        $this->startedAt = $startedAt;
        $this->expiresAt = $expiresAt;
        $this->duration = $duration;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('public-ruleta');
    }

    public function broadcastAs(): string
    {
        return 'turno.timer';
    }
}
