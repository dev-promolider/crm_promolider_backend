<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dinamica extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'nombre',
        'tipo_dinamica',
        'descripcion',
        'modo_inscripcion',
        'tiempo_inscripcion',
        'max_participantes',
        'mostrar_inscritos',
        'tipo_premio',
        'max_ganadores',
        'slug',
        'is_public',
        'is_active',
        'activated_at',
        'registration_closes_at',
        'estado',
    ];

    protected $casts = [
        'mostrar_inscritos' => 'boolean',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'activated_at' => 'datetime',
        'registration_closes_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function premios()
    {
        return $this->hasMany(DinamicaPremio::class);
    }

    public function triviaConfig()
    {
        return $this->hasOne(DinamicaTriviaConfig::class);
    }
}
