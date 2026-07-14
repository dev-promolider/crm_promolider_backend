<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    use HasFactory;

    protected $table = 'payment_links';

    protected $fillable = [
        'name',
        'slug',
        'product_type',
        'product_id',
        'amount',
        'description',
        'active',
        'usage_count',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'active' => 'boolean',
        'usage_count' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function (self $link) {
            if (empty($link->slug)) {
                $link->slug = Str::slug($link->name);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
