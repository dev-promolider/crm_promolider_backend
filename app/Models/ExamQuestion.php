<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $table = 'exam_question';

    protected $fillable = [
        'exam_id',
        'title',
        'options',
        'points',
        'correct',
        'question_type_id',
        'type',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'correct' => 'string',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
