<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuestionAnswer extends Model
{
    use HasFactory;

    protected $table = "user_question_answer";
    protected $fillable = [
        'user_exam_id',
        'options_selected',
        'points_gained',
    ];

    protected $casts = [
        'options_selected' => 'array'
    ];
}
