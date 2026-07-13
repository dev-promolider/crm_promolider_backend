<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuestionAnswer extends Model
{
    use HasFactory;

    protected $table = 'user_question_answer';

    protected $fillable = [
        'user_exam_id',
        'points_gained',
        'options_selected',
    ];

    protected $casts = [
        'points_gained' => 'float',
        'options_selected' => 'array',
    ];
}
