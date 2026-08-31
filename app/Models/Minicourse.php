<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Minicourse extends Model
{
    protected $table = 'mini_courses';

    protected $fillable = [
        'user_id',
        'course_id',
        'producer_id',
        'category_id',
        'title',
        'description',
        'duration',
        'level',
        'status',
        'marketplace_listed',
        'is_private',
        'landing_banner',
    ];

    public function usages()
    {
        return $this->morphMany(DistributorToolUsage::class, 'usageable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\Infoproduct\Infoproduct::class, 'course_id');
    }

    public function images()
    {
        return $this->hasMany(MiniCourseImage::class, 'mini_course_id');
    }

    public function modules()
    {
        return $this->hasMany(MiniCourseModule::class, 'mini_course_id');
    }

    public function classes()
    {
        return $this->hasMany(MiniCourseClass::class, 'mini_course_id');
    }
}
