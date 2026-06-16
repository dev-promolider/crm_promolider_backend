<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniCourseModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'mini_course_id',
        'title',
        'content',
        'duration',
    ];

    protected $casts = [
        'duration' => 'integer',
    ];

    /**
     * Relación con el mini curso
     */
    public function miniCourse()
    {
        return $this->belongsTo(MiniCourse::class);
    }
        /**
     * NUEVO: Relación con documentos del módulo
     */
    public function classes()
    {
        return $this->hasMany(MiniCourseClass::class, 'mini_course_module_id');
    }

    /**
     * Accessor para el resumen del contenido
     */
    public function getContentSummaryAttribute()
    {
        return substr($this->content, 0, 100) . '...';
    }

    /**
     * Accessor para la duración formateada
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }
    public function module()
    {
        return $this->belongsTo(MiniCourseModule::class, 'mini_course_module_id');
    }
}
