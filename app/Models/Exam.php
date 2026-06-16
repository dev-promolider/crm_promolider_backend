<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    protected $table = 'exam';
    protected $fillable = [
        "course_id",
        "productor_id",
        "module_id",
        "lesson_id",
        "title",
        "time",
        "max_score",
        "min_passing_score",
        "status",
        "created_at",
        "updated_at",
    ];

    // define a mutator for the field "time"
    public function setTimeAttribute($value)
    {
        $this->attributes['time'] = (int) $value;
    }
}
