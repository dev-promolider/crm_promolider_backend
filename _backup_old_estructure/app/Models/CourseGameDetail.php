<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseGameDetail extends Model
{
    use HasFactory;
    protected $table = 'course_game_detail';
    protected $fillable = [
        "game_id",
        "data",
        "status",
        "created_at",
        "updated_at",
    ];
    protected $casts = [
        'data' => 'array'
    ];
}
