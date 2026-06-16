<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'design_data',
        'html_template',
        'preview_image',
        'is_active',
    ];

    protected $casts = [
        'design_data' => 'array',
        'is_active' => 'boolean',
    ];

    // 👇 Esto hará que siempre aparezca en tu JSON
    protected $appends = ['preview_image_url'];

    public function certificates()
    {
        return $this->hasMany(CourseCertificate::class, 'template_id');
    }

    // 👇 Accessor para devolver el URL público
    public function getPreviewImageUrlAttribute()
    {
        return $this->preview_image 
            ? Storage::url($this->preview_image)
            : null;
    }
}