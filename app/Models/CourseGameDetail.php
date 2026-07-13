<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseGameDetail extends Model
{
    use HasFactory;

    protected $table = 'course_game_details';

    protected $fillable = [
        'id_course_game',
        'question',
        'answer',
        'options',
        'points',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'order' => 'integer',
    ];

    public function game()
    {
        return $this->belongsTo(CourseGame::class, 'id_course_game');
    }
}
