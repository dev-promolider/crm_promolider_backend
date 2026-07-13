<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingMasterclass extends Model
{
    protected $table = 'meeting_masterclass';

    protected $fillable = [
        'date',
        'time',
        'owner_id',
        'comments',
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'link',
        'type',
    ];
}
