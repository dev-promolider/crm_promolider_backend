<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DinamicaWinnerEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $slug;
    public $message;

    public function __construct(string $slug, string $message)
    {
        $this->slug = $slug;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('dinamica.' . $this->slug);
    }
}
