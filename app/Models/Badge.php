<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $table = 'badges';

    protected $fillable = [
        'name',
        'description',
        'level',
        'condition',
        'icon',
    ];

    protected $casts = [
        'level' => 'integer',
        'condition' => 'integer',
    ];
}
