<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DinamicaPremio extends Model
{
    use HasFactory;

    protected $fillable = [
        'dinamica_id',
        'nombre',
        'tipo',
        'stock',
        'peso',
        'limite_usuario',
        'vigencia_inicio',
        'vigencia_fin',
        'claim_url',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fin' => 'date',
    ];

    public function dinamica()
    {
        return $this->belongsTo(Dinamica::class);
    }
}
