<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'cost',
        'status',
        'notes',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que canjeó
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el premio canjeado
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Relación con el usuario que procesó el canje
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}