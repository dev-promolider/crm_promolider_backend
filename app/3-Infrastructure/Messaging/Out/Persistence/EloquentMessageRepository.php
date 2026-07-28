<?php

namespace Promolider\Infrastructure\Messaging\Out\Persistence;

use App\Models\Message as EloquentMessage;
use App\Models\User as EloquentUser;
use Promolider\Domain\Messaging\Entities\Message as MessageEntity;
use Promolider\Domain\Messaging\Ports\Out\MessageRepositoryInterface;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    public function getRecentMessagesByUser(int $userId): array
    {
        $msj = EloquentMessage::select(
                "users.name as fullname",
                "users.email",
                "messages.transmitter_id",
                "messages.message",
                "messages.created_at"
            )
            ->join("users", "messages.transmitter_id", "=", "users.id")
            ->where('messages.receiver_id', $userId)
            ->orderBy('messages.created_at', 'DESC')
            ->get()
            ->groupBy('transmitter_id');

        $json = [];
        foreach ($msj as $group) {
            if (count($json) >= 5) {
                break;
            }
            if ($first = $group->first()) {
                $json[] = [
                    'fullname' => $first->fullname,
                    'email' => $first->email,
                    'transmitter_id' => $first->transmitter_id,
                    'message' => $first->message,
                    'created_at' => $first->created_at ? $first->created_at->toDateTimeString() : null,
                ];
            }
        }

        return $json;
    }

    public function getConversationWithUser(int $userId, string $email): array
    {
        $otherUser = EloquentUser::where('email', $email)->first();
        if (!$otherUser) {
            return [];
        }

        $messages = EloquentMessage::select('users.name', 'messages.message', 'messages.created_at')
            ->join('users', 'users.id', '=', 'messages.transmitter_id')
            ->where(function ($query) use ($userId, $otherUser) {
                $query->where([
                    ['messages.transmitter_id', '=', $userId],
                    ['messages.receiver_id', '=', $otherUser->id]
                ])->orWhere([
                    ['messages.transmitter_id', '=', $otherUser->id],
                    ['messages.receiver_id', '=', $userId]
                ]);
            })
            ->orderBy('messages.created_at', 'ASC')
            ->get();

        return $messages->toArray();
    }

    public function createMessage(int $transmitterId, int $receiverId, string $message): MessageEntity
    {
        $model = EloquentMessage::create([
            'transmitter_id' => $transmitterId,
            'receiver_id' => $receiverId,
            'message' => $message,
        ]);

        return new MessageEntity(
            $model->id,
            $model->transmitter_id,
            $model->receiver_id,
            $model->message,
            $model->created_at ? $model->created_at->toDateTimeString() : null
        );
    }
}
