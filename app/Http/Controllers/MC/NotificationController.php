<?php

namespace App\Http\Controllers\MC;

use App\Http\Controllers\Controller;
use App\Models\MasterClassNotification;

class NotificationController extends Controller
{
    public function list()
    {
        $userId = auth()->id();
    
        $notifications = MasterClassNotification::where('receiver', $userId)
            ->latest()
            ->get();
    
        return response()->json($notifications, 200);
    }

    public function markAsSeen($id)
    {
        $notification = MasterClassNotification::where('id', $id)->first();
        $notification->seen = true;
        $notification->save();
        return response()->json(['message' => 'Notificación marcada como leída'], 200);
    }
}
