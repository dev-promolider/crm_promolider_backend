<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DinamicaRegistro extends Model
{
    use HasFactory;

    protected $fillable = [
        'dinamica_id',
        'nombre',
        'apellido',
        'email',
        'turno',
        'ha_jugado',
        'ha_ganado',
        'turno_inicio',
        'premio_ganado',
    ];

    protected $casts = [
        'turno_inicio' => 'datetime',
    ];

    public function dinamica()
    {
        return $this->belongsTo(Dinamica::class);
    }

    public function respuestas()
    {
        return $this->hasMany(UserQuestionAnswer::class, 'dinamica_registro_id');
    }
}
