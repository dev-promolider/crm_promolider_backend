<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMasterclass extends Model
{
    use HasFactory;
    protected $table = 'meeting_masterclass';
    protected $fillable = [
        'date',
        'time',
        'owner_id',
        'comments',
        'user_id',
    ];
}
