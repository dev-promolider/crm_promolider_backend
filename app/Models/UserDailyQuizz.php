<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDailyQuizz extends Model
{
    use HasFactory;

    protected $table = 'user_daily_quizzs';
    protected $guarded = [];
}
