<?php

namespace App\Models;

use App\Helpers\ParseUrl;
use App\Models\Infoproduct\Book\BookFile;
use App\Models\Infoproduct\Book\BookObservation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;
    
    protected $table = 'courses';
    
    protected $fillable = [
        "id",
        "user_id",
        "product_type_id",
        "id_categories",
        "title",
        "slug",
        "area",
        "description",
        "image",
        "currency",
        "price",
        "price_base",
        "ranking_by_user",
        "status",
        "course_for",
        "course_about",
        "course_level_id",
        "portada",
        "url_portada",
        "will_learn",
        "prev_knowledge",
        "path_url", // Para el video/imagen promocional
        "price_base",
        "certificate",
        "instructor_signature_path",
        "months"
    ];

    protected $casts = [
        'certificate' => 'boolean',
    ];

    // ✅ Ya tienes esto y está bien
    public function getUrlPortadaAttribute($value)
    {
        if (!$value) return null;
        return ParseUrl::contacAtrrS3($value);
    }

    // ✅ Ya tienes esto y está bien  
    public function getPathUrlAttribute($value)
    {
        if (!$value) return null;
        return ParseUrl::contacAtrrS3($value);
    }

    // ✅ Método adicional útil para saber si tiene video promocional
    public function hasPromoVideo()
    {
        return $this->path_url && str_ends_with(strtolower($this->path_url), '.mp4');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function certificate_template()
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    // ✅ Método adicional útil para saber si tiene imagen promocional
    public function hasPromoImage()
    {
        if (!$this->path_url) return false;
        
        $imageExtensions = ['.jpg', '.jpeg', '.png', '.webp'];
        $extension = strtolower(substr($this->path_url, strrpos($this->path_url, '.')));
        
        return in_array($extension, $imageExtensions);
    }

    // Tus relaciones existentes...
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class, 'id_courses');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // ✅ Corregido: era Module
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(BookFile::class, 'course_id');
    }

    public function bookObservations(): HasMany
    {
        return $this->hasMany(BookObservation::class, 'course_id');
    }
}