<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;
    protected $table = 'exam_question';
    protected $fillable = [
        "exam_id",
        "title",
        "options",
        "points",
        "type",
        "created_at",
        "updated_at",
    ];

    protected $casts = [
        'options' => 'array'
    ];

}
