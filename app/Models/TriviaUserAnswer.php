<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriviaUserAnswer extends Model
{
    use HasFactory;

    protected $table = 'trivia_user_answers';

    protected $fillable = [
        'dinamica_id',
        'dinamica_registro_id',
        'question_item_id',
        'numero_pregunta',
        'opcion_indice',
        'opcion_texto',
        'es_correcta',
        'valor_pregunta',
        'puntos_obtenidos',
        'tiempo_respuesta',
    ];

    protected $casts = [
        'es_correcta' => 'boolean',
        'tiempo_respuesta' => 'float',
        'puntos_obtenidos' => 'float',
        'valor_pregunta' => 'float',
    ];

    public function dinamica()
    {
        return $this->belongsTo(Dinamica::class, 'dinamica_id');
    }

    public function registro()
    {
        return $this->belongsTo(DinamicaRegistro::class, 'dinamica_registro_id');
    }
}
