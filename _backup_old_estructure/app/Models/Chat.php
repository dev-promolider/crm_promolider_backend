<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "chats";

    protected $fillable = [
        "title",
        "param",
        "level",
        "status",
        "user_id"
    ];

    protected $dates=['deleted_at'];

    public function details()
    {
        return $this->hasMany(ChatDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
