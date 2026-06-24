<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DinamicaSpinEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $slug;
    public array $premio;

    public function __construct(string $slug, array $premio)
    {
        $this->slug = $slug;
        $this->premio = $premio;
    }

    public function broadcastOn()
    {
        return new Channel('dinamica.' . $this->slug);
    }
}
