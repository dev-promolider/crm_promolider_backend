<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRate extends Model
{
    use HasFactory;

    protected $table = 'course_rates';

    protected $fillable = [
        'id_user',
        'id_courses',
        'points',
        'commentary',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_courses');
    }
}
