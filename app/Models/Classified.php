<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Classified extends Model
{
    use HasFactory;

    protected $table = 'classified';
    protected $guarded = [];

    /**
     * El usuario colocado en esta posicion del arbol.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quien lo patrocino (no necesariamente el que tiene encima en el arbol).
     */
    public function userSponsor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user_sponsor');
    }
}
