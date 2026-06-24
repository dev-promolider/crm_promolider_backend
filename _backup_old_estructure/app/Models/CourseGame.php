<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseGame extends Model
{
    use HasFactory;
    protected $table = 'course_games';
    protected $primaryKey = 'id';
    protected $fillable = [
        "game_type_id",
        "course_id",
        "module_id",
        "lesson_id",
        "title",
        "status",
        "created_at",
        "updated_at",
    ];
}
