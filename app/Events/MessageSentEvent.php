<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.conversation.' . $this->message->conversation_id);
    }

    /**
     * Get the name of the event to broadcast.
     */
    public function broadcastAs()
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        $transmitter = $this->message->transmitter;

        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'transmitter_id'  => $this->message->transmitter_id,
            'receiver_id'     => $this->message->receiver_id,
            'message'         => $this->message->message,
            'created_at'      => $this->message->created_at,
            'transmitter'     => $transmitter ? [
                'id'        => $transmitter->id,
                'name'      => $transmitter->name,
                'last_name' => $transmitter->last_name,
                'photo'     => $transmitter->photo,
            ] : null,
        ];
    }
}
