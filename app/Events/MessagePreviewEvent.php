<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso ligero al canal del usuario receptor para actualizar en tiempo real
 * el preview y el contador de no leídos de conversaciones que NO están
 * abiertas (el canal chat.conversation.{id} solo lo escucha el chat abierto).
 */
class MessagePreviewEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $receiverId;

    public array $payload;

    public function __construct(int $receiverId, array $payload)
    {
        $this->receiverId = $receiverId;
        $this->payload = $payload;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->receiverId);
    }

    /**
     * The name of the event to broadcast.
     */
    public function broadcastAs()
    {
        return 'message.received';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return $this->payload;
    }
}
