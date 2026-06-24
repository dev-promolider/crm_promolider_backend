<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniCourseImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'mini_course_id',
        'image',
    ];

    /**
     * Relación con el mini curso
     */
    public function miniCourse()
    {
        return $this->belongsTo(MiniCourse::class);
    }

    /**
     * Accessor para la URL completa de la imagen
     */
    public function getImageUrlAttribute()
    {
        return asset($this->image);
    }
}
