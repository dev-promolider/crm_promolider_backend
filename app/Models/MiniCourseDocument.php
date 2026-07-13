<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniCourseDocument extends Model
{
    protected $table = 'mini_course_documents';

    protected $fillable = [
        'mini_course_id',
        'mini_course_class_id',
        'document',
    ];

    public function class()
    {
        return $this->belongsTo(MiniCourseClass::class, 'mini_course_class_id');
    }
}
