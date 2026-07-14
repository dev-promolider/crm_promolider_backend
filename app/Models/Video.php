<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'path',
        'filename',
        'videoable_type',
        'videoable_id',
        'class_id',
        'saved_time',
        'status',
    ];

    protected $casts = [
        'saved_time' => 'float',
        'status' => 'integer',
    ];

    public function videoable()
    {
        return $this->morphTo();
    }

    public function clas()
    {
        return $this->belongsTo(Clas::class, 'class_id');
    }
}
