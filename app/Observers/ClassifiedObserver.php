<?php

namespace App\Observers;

use App\Models\Classified;
use App\Services\MLM\BinaryTreeService;

/**
 * La fila de 'classified' es la que dice donde cuelga cada usuario del arbol binario.
 * Es este cambio, y no el alta del usuario, el que deja el arbol cacheado obsoleto.
 */
class ClassifiedObserver
{
    public function created(Classified $classified): void
    {
        BinaryTreeService::invalidateCache();
    }

    public function updated(Classified $classified): void
    {
        if ($classified->isDirty(['user_above', 'position', 'id_user_sponsor'])) {
            BinaryTreeService::invalidateCache();
        }
    }

    public function deleted(Classified $classified): void
    {
        BinaryTreeService::invalidateCache();
    }
}
