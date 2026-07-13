<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniCourseClass extends Model
{
    protected $table = 'mini_course_classes';

    protected $fillable = [
        'mini_course_id',
        'module_id',
        'video_url',
        'title',
        'description',
        'duration',
        'order',
    ];

    public function miniCourse()
    {
        return $this->belongsTo(Minicourse::class, 'mini_course_id');
    }

    public function module()
    {
        return $this->belongsTo(MiniCourseModule::class, 'module_id');
    }

    public function documents()
    {
        return $this->hasMany(MiniCourseDocument::class, 'mini_course_class_id');
    }
}
