<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaypalAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'account_name',
        'country_code',
        'currency',
        'account_type',
        'is_verified',
        'is_active',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope para cuentas activas
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope para cuentas verificadas
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Accessor para mostrar el tipo
    public function getPaymentTypeAttribute()
    {
        return 'PayPal';
    }
}
