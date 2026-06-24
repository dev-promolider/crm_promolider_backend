<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriviaUserAnswer extends Model
{
    use HasFactory;

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
        'valor_pregunta' => 'float',
        'puntos_obtenidos' => 'float',
        'tiempo_respuesta' => 'float',
    ];

    public function registro()
    {
        return $this->belongsTo(DinamicaRegistro::class, 'dinamica_registro_id');
    }

    public function dinamica()
    {
        return $this->belongsTo(Dinamica::class, 'dinamica_id');
    }

    public function question()
    {
        return $this->belongsTo(QuestionItem::class, 'question_item_id');
    }
}
