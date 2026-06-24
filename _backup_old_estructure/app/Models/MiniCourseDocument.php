<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniCourseDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'mini_course_id',
        'document',
        'mini_course_class_id',
    ];

    /**
     * Relación con el mini curso
     */
    public function miniCourse()
    {
        return $this->belongsTo(MiniCourse::class);
    }

    /**
     * Accessor para la URL completa del documento
     */
    public function getDocumentUrlAttribute()
    {
        return asset($this->document);
    }
    public function module()
    {
        return $this->belongsTo(MiniCourseClass::class, 'mini_course_class_id');
    }
}
