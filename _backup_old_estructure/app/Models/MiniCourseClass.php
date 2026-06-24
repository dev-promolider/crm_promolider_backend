<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniCourseClass extends Model
{
    use HasFactory;

    protected $table = 'mini_course_classes';

    protected $fillable = [
        'mini_course_id',
        'mini_course_module_id',
        'video',
        'title',
        'description',
        'duration',
        'order',
    ];

    protected $casts = [
        'duration' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Relación con el mini curso
     */
    public function miniCourse()
    {
        return $this->belongsTo(MiniCourse::class);
    }

    public function documents()
    {
        return $this->hasMany(MiniCourseDocument::class);
    }

    /**
     * Accessor para la URL completa del video
     */
    public function getVideoUrlAttribute()
    {
        return asset($this->video);
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

    /**
     * Accessor para el resumen de la descripción
     */
    public function getDescriptionSummaryAttribute()
    {
        if (!$this->description) {
            return null;
        }
        
        return strlen($this->description) > 100 
            ? substr($this->description, 0, 100) . '...' 
            : $this->description;
    }

    /**
     * Scope para ordenar videos por orden
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
