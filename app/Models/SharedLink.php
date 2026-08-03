<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedLink extends Model
{
    use HasFactory;

    protected $table = 'sponsor_link';
    protected $guarded = ['id', 'user_id', 'status', 'created_at', 'updated_at'];
}
