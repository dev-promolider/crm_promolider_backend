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
}
