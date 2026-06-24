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

    /**
     * Relación con el usuario que es el distribuidor
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el minicurso que distribuye
     */
    public function miniCourse()
    {
        return $this->belongsTo(MiniCourse::class, 'mini_course_id');
    }
}
