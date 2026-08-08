<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function video()
    {
        return $this->hasOne(Video::class, 'class_id');
    }
}
