<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatDetail extends Model
{
    use HasFactory;

    protected $table = "chats_details";

    protected $fillable = [
        "ask",
        "answer",
        "status",
        "chat_id"
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }
}
