<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'type',
        'content',
        'file_path',
        'file_name',
        'status',
    ];
}
