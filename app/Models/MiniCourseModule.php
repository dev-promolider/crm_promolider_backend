<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniCourseModule extends Model
{
    protected $table = 'mini_course_modules';

    protected $fillable = [
        'mini_course_id',
        'title',
        'content',
        'duration',
    ];

    public function miniCourse()
    {
        return $this->belongsTo(Minicourse::class, 'mini_course_id');
    }

    public function classes()
    {
        return $this->hasMany(MiniCourseClass::class, 'module_id');
    }
}
