<?php

namespace App\Models\Infoproduct\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $table = 'class'; // Tabla asociada al modelo Eloquent

    protected $fillable = [
        "id",
        "id_modules",
        "name",
        "slug",
        "time",
        "url",
        "description",
        "order",
        "status",
        "progress"
    ];
}
