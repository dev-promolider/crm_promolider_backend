<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Video;
use App\Models\ClassResource;

class Clas extends Model
{
    use HasFactory;

    protected $table = 'class';

    protected $fillable = [
        'id_courses',
        'id_modules',
        'name',
        'description',
        'video',
        'path_url',
        'time',
        'order',
        'status',
        'resource',
    ];

    protected $casts = [
        'order' => 'integer',
        'status' => 'integer',
        'time' => 'integer',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'id_modules');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_courses');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(
            ClassResource::class,
            'class_id'
        );
    }

    public function video(): MorphOne
    {
        return $this->morphOne(
            Video::class,
            'videoable'
        );
    }
}
