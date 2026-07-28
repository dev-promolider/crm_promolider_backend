<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';
    protected $fillable = [
        'transmitter_id',
        'receiver_id',
        'message',
    ];
    protected $hidden = ['updated_at'];

    public function scopeMessageOrder($query)
    {
        $userId = auth()->user()?->id;
        return $query->where('messages.receiver_id', $userId)
            ->orderBy('messages.created_at', 'DESC')
            ->get()
            ->groupBy(function ($data) {
                return $data->transmitter_id;
            });
    }

    public function scopeMessageSelect($query)
    {
        return $query->select(
                "users.name as fullname",
                "users.email",
                "messages.transmitter_id",
                "messages.message",
                "messages.created_at"
            )
            ->join("users", "messages.transmitter_id", "=", "users.id");
    }

    public function transmitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transmitter_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
