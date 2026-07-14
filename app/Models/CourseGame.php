<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseGame extends Model
{
    use HasFactory;

    protected $table = 'course_games';

    protected $fillable = [
        'id_courses',
        'id_modules',
        'id_lesson',
        'name',
        'description',
        'type',
        'config',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'config' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_courses');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_modules');
    }

    public function lesson()
    {
        return $this->belongsTo(Clas::class, 'id_lesson');
    }

    public function details()
    {
        return $this->hasMany(CourseGameDetail::class, 'id_course_game');
    }
}
