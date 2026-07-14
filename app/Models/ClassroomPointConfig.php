<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomPointConfig extends Model
{
    use HasFactory;

    protected $table = 'classroom_point_configs';

    protected $fillable = [
        'passed_course',
        'daily_question',
        'achievement',
    ];

    protected $casts = [
        'passed_course' => 'float',
        'daily_question' => 'float',
        'achievement' => 'float',
    ];
}
