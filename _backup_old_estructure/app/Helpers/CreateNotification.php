<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MasterClassNotification;
use App\Models\User;

class CreateNotification
{
    public static function saveNotification(Request $request)
    {
        $rootUser = User::first(); # admin user
        $notification = new MasterClassNotification();
        $notification->transmitter = $rootUser->id; # If transmitter is not set, then the transmitter will be the admin user
        $notification->receiver = $request->receiver;
        $notification->title = $request->title;
        $notification->body = $request->body;
        if ($request->has('url')) {
            $notification->url = $request->url;
        }
        if ($request->has('icon')) {
            $notification->icon = $request->icon;
        }
        $notification->save();
        if ($notification->save()) {
            return true;
        }
        return false;
    }

    public static function saveNotificationDistributors($creatorId, $titleOrName, $type)
    {
        $messages = [
            'masterclass' => [
                'title' => 'Se ha creado una nueva masterclass',
                'body'  => "Se ha creado la masterclass {$titleOrName}",
            ],
            'ebook' => [
                'title' => 'Se ha publicado un nuevo ebook',
                'body'  => "Se ha publicado el ebook {$titleOrName}",
            ],
            'minicourse' => [
                'title' => 'Se ha lanzado un nuevo minicurso',
                'body'  => "Se ha lanzado el minicurso {$titleOrName}",
            ],
        ];

        if (!isset($messages[$type])) {
            throw new \InvalidArgumentException("Tipo de notificación inválido: {$type}");
        }

        // Obtener distribuidores
        $distributorIds = User::role('Distributor')->pluck('id')->toArray();

        foreach ($distributorIds as $receiverId) {
            MasterClassNotification::create([
                'transmitter' => $creatorId,
                'receiver'    => $receiverId,
                'title'       => $messages[$type]['title'],
                'body'        => $messages[$type]['body'],
            ]);
        }

        return true;
    }
}
