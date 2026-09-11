<?php

namespace App\Models\Infoproduct;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Infoproduct extends Model
{
    use HasFactory;

    protected $table = 'courses'; // Tabla asociada al modelo Eloquent

    protected $fillable = [
        "id",
        "user_id",
        "product_type_id",
        "id_categories",
        "title",
        "language",
        "reading_mode",
        "slug",
        "area",
        "description",
        "includes",
        "image",
        "currency",
        "price",
        "price_base",
        "old_price",
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
        'includes' => 'array',
    ];
}
