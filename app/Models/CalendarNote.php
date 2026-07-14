<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarNote extends Model
{
    protected $table = 'calendar_notes';

    protected $fillable = [
        'user_id',
        'date',
        'time',
        'content',
    ];

    protected $appends = ['text', 'time_string'];

    protected $casts = [
        'time' => 'datetime:H:i',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTextAttribute()
    {
        return $this->content;
    }

    public function getTimeStringAttribute()
    {
        return $this->time ? $this->time->format('H:i') : '00:00';
    }
}
