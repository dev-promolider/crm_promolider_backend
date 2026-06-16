<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasedCourse extends Model
{
    use HasFactory;

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

    // Un PurchasedCourse pertenece a un curso
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    // Un PurchasedCourse pertenece a un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}