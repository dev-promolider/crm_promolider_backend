<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commnent extends Model
{
    protected $table = 'commentary';
    protected $fillable = [
        'issuing_user_id',
        'receiving_user_id',
        'class_id',
        'comments'
    ];
}
