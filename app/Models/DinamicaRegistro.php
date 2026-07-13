<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DinamicaRegistro extends Model
{
    use HasFactory;

    protected $table = 'dinamica_registros';

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
        'ha_jugado' => 'boolean',
        'ha_ganado' => 'boolean',
    ];

    public function dinamica()
    {
        return $this->belongsTo(Dinamica::class, 'dinamica_id');
    }

    public function respuestas()
    {
        return $this->hasMany(TriviaUserAnswer::class, 'dinamica_registro_id');
    }
}
