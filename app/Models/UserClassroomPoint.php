<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClassroomPoint extends Model
{
    use HasFactory;

    protected $table = 'user_classroom_points';
    protected $guarded = ['id', 'user_id', 'total_points', 'created_at', 'updated_at'];
}
