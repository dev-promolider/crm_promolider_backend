<?php

namespace Promolider\Infrastructure\Marketing\In\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RuletaSpinEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $angle;
    public string $slug;
    public int $registroId;

    public function __construct(int $angle, string $slug, int $registroId)
    {
        $this->angle = $angle;
        $this->slug = $slug;
        $this->registroId = $registroId;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('public-ruleta');
    }

    public function broadcastAs(): string
    {
        return 'ruleta.spin';
    }
}
