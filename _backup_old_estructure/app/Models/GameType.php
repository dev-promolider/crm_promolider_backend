<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    use HasFactory;
    protected $table = 'games_types';
    protected $fillable = [
        "title",
        "created_at",
        "updated_at",
    ];
}
