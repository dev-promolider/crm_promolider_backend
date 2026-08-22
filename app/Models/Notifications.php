<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    /**
     * Tipo exclusivo para notificaciones de mensajes del chat (Aula Virtual).
     * La columna legada `notifications.type` es TINYINT con 0-3 ya ocupados,
     * se usa 99 para el chat. El CRM filtra este tipo para no mostrarlo.
     */
    public const TYPE_CHAT_MESSAGE = 99;

    protected $table = 'notifications';
    protected $guarded = [];
}
