<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomPointDetail extends Model
{
    use HasFactory;

    protected $table = 'classroom_point_details';

    protected $fillable = [
        'id_user_classroom_points',
        'increment_points',
        'description',
        'completion_time',
    ];

    protected $casts = [
        'increment_points' => 'integer',
        'completion_time' => 'integer',
    ];

    public function userClassroomPoint()
    {
        return $this->belongsTo(UserClassroomPoint::class, 'id_user_classroom_points');
    }
}
