<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLessonProgress extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'lesson_id', 'completed', 'completed_at'];
    
    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];
}