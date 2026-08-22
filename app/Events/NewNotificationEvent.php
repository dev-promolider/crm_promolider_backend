<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewNotificationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $title;
    public $body;
    public $type;
    public $photo;
    public $id_receiver;

    /**
     * Datos originales (incluye extras como id_generator / conversation_id).
     */
    public array $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'];
        $this->body = $data['body'];
        $this->type = $data['type'] ?? 'info';
        $this->photo = $data['photo'] ?? null;
        $this->id_receiver = $data['id_receiver'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->id_receiver);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'photo' => $this->photo,
            'id_generator' => $this->data['id_generator'] ?? null,
            'conversation_id' => $this->data['conversation_id'] ?? null,
            'created_at' => now()->toISOString(),
        ];
    }
}
