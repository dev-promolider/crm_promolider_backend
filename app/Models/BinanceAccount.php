<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BinanceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'account_name',
        'binance_id',
        'phone',
        'network',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getPaymentTypeAttribute()
    {
        return 'Binance';
    }
}
