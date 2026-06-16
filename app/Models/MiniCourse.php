<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiniCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'duration',
        'level',
        'status',
    ];

    protected $casts = [
        'duration' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Los niveles disponibles
     */
    const LEVELS = [
        'principiante' => 'Principiante',
        'intermedio' => 'Intermedio',
        'avanzado' => 'Avanzado',
    ];

    /**
     * Relación con el usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la categoría
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con las imágenes del mini curso
     */
    public function images()
    {
        return $this->hasMany(MiniCourseImage::class);
    }

    /**
     * Relación con los videos del mini curso
     */
    public function classes()
    {
        return $this->hasMany(MiniCourseClass::class)->ordered();
    }

    /**
     * Relación con los módulos del mini curso
     */
    public function modules()
    {
        return $this->hasMany(MiniCourseModule::class);
    }

    /**
     * Relación con los distribuidores
     */
    public function distributors()
    {
        return $this->hasMany(MiniCourseDistributor::class, 'mini_course_id');
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
     * Accessor para el nivel formateado
     */
    public function getFormattedLevelAttribute()
    {
        return self::LEVELS[$this->level] ?? $this->level;
    }

    /**
     * Accessor para obtener el total de videos
     */
    public function getTotalVideosAttribute()
    {
        return $this->classes()->count();
    }

    /**
     * Accessor para obtener la duración total de videos
     */
    public function getTotalVideosDurationAttribute()
    {
        return $this->classes()->sum('duration') ?? 0;
    }

    /**
     * Accessor para la duración total de videos formateada
     */
    public function getFormattedTotalVideosDurationAttribute()
    {
        $totalDuration = $this->getTotalVideosDurationAttribute();
        $hours = floor($totalDuration / 60);
        $minutes = $totalDuration % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }

    /**
     * Scope para mini cursos activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope para mini cursos de un usuario específico
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para mini cursos por nivel
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope para mini cursos que tienen videos
     */
    public function scopeWithVideos($query)
    {
        return $query->whereHas('videos');
    }
}