<?php

namespace Promolider\Infrastructure\Marketing\In\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DinamicaWinnerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $slug;
    public string $message;
    public ?string $premio;

    public function __construct(string $slug, string $message, ?string $premio = null)
    {
        $this->slug = $slug;
        $this->message = $message;
        $this->premio = $premio;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('dinamica.' . $this->slug);
    }

    public function broadcastAs(): string
    {
        return 'dinamica.winner';
    }
}
