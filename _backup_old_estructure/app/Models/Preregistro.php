<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preregistro extends Model
{
    use HasFactory;

    protected $table = 'preregistros';

    protected $fillable = [
        'nombres',
        'apellidos',
        'correo',
        'whatsapp',
        'referrer_username',
        'lado',
    ];
}