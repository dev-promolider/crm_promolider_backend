<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DinamicaTurno extends Model
{
    use HasFactory;

    protected $fillable = [
        'dinamica_id',
        'registro_id',
        'turno_orden',
        'started_at',
        'expires_at',
        'ended_at',
        'estado',
        'premio_nombre',
        'premio_tipo',
        'angulo',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function dinamica()
    {
        return $this->belongsTo(Dinamica::class);
    }

    public function registro()
    {
        return $this->belongsTo(DinamicaRegistro::class, 'registro_id');
    }
}
