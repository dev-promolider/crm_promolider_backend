<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'filename',
        'videoable_type',
        'videoable_id',
        'class_id',
        'saved_time',
    ];

    public function videoable()
    {
        return $this->morphTo();
    }
}
