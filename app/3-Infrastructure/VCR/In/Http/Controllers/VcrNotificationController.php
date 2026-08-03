<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Notifications;
use Illuminate\Routing\Controller;

class VcrNotificationController extends Controller
{
    /**
     * GET /api/v1/notifications/list
     */
    public function list()
    {
        $user_id = auth()->user()->id;
        $notifications = Notifications::join('users', 'users.id', '=', 'id_generator')
            ->where('id_receiver', $user_id)
            ->select('notifications.id', 'users.photo', 'title', 'body', 'type', 'notifications.created_at')
            ->orderBy('notifications.created_at', 'desc')
            ->get();

        foreach ($notifications as $notification) {
            $notification->photo = ParseUrl::contacAtrrS3($notification->photo);
        }

        return response()->json($notifications);
    }
}
