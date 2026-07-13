<?php

namespace App\Models\Infoproduct\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules'; // Tabla asociada al modelo Eloquent

    protected $fillable = [
        "id",
        "id_courses",
        "name",
        "description",
        "order",
        "status"
    ];
}
