<?php

namespace App\Observers;

use App\Models\Notifications;
use App\Events\NewNotificationEvent;

class NotificationObserver
{
    /**
     * Handle the Notifications "created" event.
     *
     * @param  \App\Models\Notifications  $notification
     * @return void
     */
    public function created(Notifications $notification)
    {
        // Enviar evento de WebSockets para notificar en tiempo real al usuario
        if ($notification->id_receiver) {
            try {
                broadcast(new NewNotificationEvent([
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'type' => $notification->type,
                    'photo' => null,
                    'id_receiver' => $notification->id_receiver,
                ]))->toOthers();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Could not broadcast notification: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Notifications "updated" event.
     *
     * @param  \App\Models\Notifications  $notification
     * @return void
     */
    public function updated(Notifications $notification)
    {
        //
    }

    /**
     * Handle the Notifications "deleted" event.
     *
     * @param  \App\Models\Notifications  $notification
     * @return void
     */
    public function deleted(Notifications $notification)
    {
        //
    }

    /**
     * Handle the Notifications "restored" event.
     *
     * @param  \App\Models\Notifications  $notification
     * @return void
     */
    public function restored(Notifications $notification)
    {
        //
    }

    /**
     * Handle the Notifications "force deleted" event.
     *
     * @param  \App\Models\Notifications  $notification
     * @return void
     */
    public function forceDeleted(Notifications $notification)
    {
        //
    }
}
