<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dinamica extends Model
{
    use HasFactory;

    protected $table = 'dinamicas';

    protected $fillable = [
        'user_id',
        'course_id',
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

    public function premios()
    {
        return $this->hasMany(DinamicaPremio::class, 'dinamica_id');
    }

    public function registros()
    {
        return $this->hasMany(DinamicaRegistro::class, 'dinamica_id');
    }

    public function triviaConfig()
    {
        return $this->hasOne(DinamicaTriviaConfig::class, 'dinamica_id');
    }

    public function turnos()
    {
        return $this->hasMany(DinamicaTurno::class, 'dinamica_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\Infoproduct\Infoproduct::class, 'course_id');
    }
}
