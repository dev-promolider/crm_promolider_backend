<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniCourseUser extends Model
{
    use HasFactory;

    protected $table = 'mini_course_users';

    protected $fillable = [
        'mini_course_distributors_id',
        'name',
        'lastname',
        'email',
        'phone',
        'nationality',
        'age',
        'access_token',
        'token_expires_at',
        'last_accessed_at',
    ];

    // Relación con el distribuidor
    public function distributor()
    {
        return $this->belongsTo(MiniCourseDistributor::class, 'mini_course_distributors_id');
    }
}