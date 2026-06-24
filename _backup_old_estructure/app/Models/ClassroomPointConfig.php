<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomPointConfig extends Model
{
    use HasFactory;
    protected $table = 'classroom_point_configs';
    protected $fillable = [
        "id",
        "passed_course",
        "daily_question",
        "achievement",
    ];
}
