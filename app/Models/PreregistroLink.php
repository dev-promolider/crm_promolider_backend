<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreregistroLink extends Model
{
    protected $table = 'preregistro_links';

    protected $fillable = [
        'username',
        'lado',
        'landing',
        'is_active',
    ];
}