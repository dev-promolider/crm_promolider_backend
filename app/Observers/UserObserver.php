<?php

namespace App\Observers;

use App\Models\User;
use App\Services\MLM\BinaryTreeService;

/**
 * El arbol se dibuja con datos del usuario (estado, membresia, tipo de cuenta) y con
 * su fila en 'classified'. De la colocacion se encarga ClassifiedObserver; aqui solo
 * se vigilan los campos del usuario que cambian lo que el arbol muestra.
 */
class UserObserver
{
    /**
     * Columnas que afectan a como sale el usuario en el arbol.
     *
     * Antes se comprobaban 'parent_id', 'left_points' y 'right_points', que no existen
     * en la tabla users: la condicion nunca se cumplia y el arbol no se refrescaba nunca.
     */
    private const CAMPOS_RELEVANTES = [
        'request',
        'id_account_type',
        'id_referrer_sponsor',
        'expiration_date',
        'expiration_membership_date',
        'name',
        'last_name',
        'photo',
    ];

    /**
     * No se invalida al crear: en ese momento el usuario todavia no tiene sitio en el
     * arbol. Lo hara ClassifiedObserver en cuanto se escriba su clasificacion.
     */
    public function created(User $user): void
    {
    }

    public function updated(User $user): void
    {
        if ($user->isDirty(self::CAMPOS_RELEVANTES)) {
            BinaryTreeService::invalidateCache();
        }
    }

    public function deleted(User $user): void
    {
        BinaryTreeService::invalidateCache();
    }
}
