<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Services;

use App\Models\Notifications;
use Illuminate\Support\Facades\DB;

final class NotificationService
{
    public function send(
        int $generatorId,
        int $receiverId,
        string $title,
        string $body,
        int $type = 3
    ): void {
        $notification = new Notifications();

        $notification->id_generator = $generatorId;
        $notification->id_receiver = $receiverId;
        $notification->title = $title;
        $notification->body = $body;
        $notification->type = $type;

        $notification->save();
    }

    /**
     * @param int[] $receiverIds
     */
    public function sendMany(
        int $generatorId,
        array $receiverIds,
        string $title,
        string $body,
        int $type = 3
    ): void {
        if (empty($receiverIds)) {
            return;
        }

        $now = now();

        $notifications = array_map(
            static function (int $receiverId) use (
                $generatorId,
                $title,
                $body,
                $type,
                $now
            ): array {
                return [
                    'id_generator' => $generatorId,
                    'id_receiver' => $receiverId,
                    'title' => $title,
                    'body' => $body,
                    'type' => $type,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            },
            $receiverIds
        );

        DB::table('notifications')->insert($notifications);
    }
}
