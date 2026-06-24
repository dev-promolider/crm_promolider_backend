<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultAvatar extends Model
{
    use HasFactory;
    protected $table = 'default_avatars';
    protected $fillable = [
        "id",
        "link",
    ];
}
