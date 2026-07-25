<?php

namespace App\Observers;

use App\Models\User;
use App\Jobs\RebuildBinaryTreeCache;

class UserObserver
{
    /**
     * Handle the User "created" event.
     * Al registrar un nuevo usuario en la red, actualizamos la caché.
     */
    public function created(User $user)
    {
        // Se despacha al Job para que se haga en background
        RebuildBinaryTreeCache::dispatch();
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user)
    {
        // Solo actualizar el árbol si cambió algo de su posición en la red (parent_id o position)
        if ($user->isDirty('parent_id') || $user->isDirty('position') || $user->isDirty('left_points') || $user->isDirty('right_points')) {
            RebuildBinaryTreeCache::dispatch();
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user)
    {
        RebuildBinaryTreeCache::dispatch();
    }
}
