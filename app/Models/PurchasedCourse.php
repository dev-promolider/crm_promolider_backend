<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasedCourse extends Model
{
    protected $table = 'purchased_courses';

    protected $fillable = [
        'user_id',
        'course_id',
        'classes_status',
        'progress',
        'last_class_reprod',
        'completed_course',
        'completed_date',
        'display_time',
        'certificate_url',
        'certificate_delivered',
        'certificate_seen',
        'lessons',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
