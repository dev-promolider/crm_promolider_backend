<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LatestLessons extends Model
{
    use HasFactory;
    protected $table = 'latest_lessons';

    public function scopeGetClass($query){
        return $query->select('id','class_id','users_id','updated_at')->where('users_id',auth()->user()->id)->orderBy('updated_at','DESC');
    }
    /**
     * Get all of the users for the LatestLessons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function course(): HasMany
    {
        return $this->hasMany(Course::class,'id_courses');
    }
    /**
     * Get the user that owns the LatestLessons
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Clas::class, 'class_id')->select('id','id_modules','name');
    }
    /**
     * Get all of the module for the LatestLessons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function module(): HasMany
    {
        return $this->hasMany(Module::class, 'id_modules');
    }
    public function scopeCountLesson($query){
        return $query->where('users_id',auth()->user()->id)->count();
    }
    public function scopeLastLesson($query){
        return $query->where('users_id',auth()->user()->id)->orderBy('updated_at', 'ASC')->first();
    }
}