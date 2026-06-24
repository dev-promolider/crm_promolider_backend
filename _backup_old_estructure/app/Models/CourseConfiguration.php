<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseConfiguration extends Model
{
    use HasFactory;
    protected $table = 'course_configuration';
    protected $fillable = [
        "id",
        "course_id",
        "data",
        "type_certificate",
        "validated_by",
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
