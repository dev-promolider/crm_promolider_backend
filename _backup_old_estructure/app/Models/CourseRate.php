<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRate extends Model
{
    use HasFactory;
    protected $table = 'course_rates';
    protected $fillable = [
        "id",
        "user_id",
        "course_id",
        "rate",
        "commentary",
    ];
}
