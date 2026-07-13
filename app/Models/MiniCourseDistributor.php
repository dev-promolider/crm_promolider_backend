<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniCourseDistributor extends Model
{
    use HasFactory;

    protected $table = 'mini_course_distributors';

    protected $fillable = [
        'user_id',
        'mini_course_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function miniCourse()
    {
        return $this->belongsTo(Minicourse::class, 'mini_course_id');
    }
}
