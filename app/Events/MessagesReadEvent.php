<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesReadEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $conversationId;

    public int $readerId;

    /** @var array<int> Ids de usuarios cuyos mensajes fueron leídos */
    public array $generatorIds;

    public function __construct(int $conversationId, int $readerId, array $generatorIds = [])
    {
        $this->conversationId = $conversationId;
        $this->readerId = $readerId;
        $this->generatorIds = array_map('intval', $generatorIds);
    }

    /**
     * Get the channels the event should broadcast on.
     * Canal del chat (otros dispositivos del chat abierto) + canal del
     * usuario (para que el panel de notificaciones quite la noti del chat).
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat.conversation.' . $this->conversationId),
            new PrivateChannel('App.Models.User.' . $this->readerId),
        ];
    }

    /**
     * Get the name of the event to broadcast.
     */
    public function broadcastAs()
    {
        return 'messages.read';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'reader_id'       => $this->readerId,
            'unread_count'    => 0,
            'generator_ids'   => $this->generatorIds,
        ];
    }
}
