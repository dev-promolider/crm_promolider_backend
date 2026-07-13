<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MiniCourseUser extends Model
{
    protected $table = 'mini_course_users';

    protected $fillable = [
        'mini_course_distributors_id',
        'name',
        'lastname',
        'email',
        'phone',
        'age',
        'nationality',
        'access_token',
        'token_expires_at',
        'last_accessed_at',
        'isParticipant',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'isParticipant' => 'boolean',
    ];

    public function distributor()
    {
        return $this->belongsTo(MiniCourseDistributor::class, 'mini_course_distributors_id');
    }
}
