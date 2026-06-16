<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomPointDetail extends Model
{
    use HasFactory;
    protected $table = 'classroom_point_details';
    protected $fillable = [
        "id",
        "id_user_classroom_points",
        "increment_points",
        "description",
    ];
}
