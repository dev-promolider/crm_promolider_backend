<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniCourseImage extends Model
{
    protected $table = 'mini_course_images';

    protected $fillable = [
        'mini_course_id',
        'image',
    ];

    public function miniCourse()
    {
        return $this->belongsTo(Minicourse::class);
    }
}
