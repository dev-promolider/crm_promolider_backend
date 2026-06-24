<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_generator',
        'id_receiver',
        'title',
        'body',
        'type',
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'id_generator');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'id_receiver');
    }

    protected static function booted()
    {
        static::created(function ($notification) {
            try {
                broadcast(new \App\Events\NotificationSentEvent($notification))->toOthers();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Error broadcasting notification event: " . $e->getMessage());
            }
        });
    }
}
