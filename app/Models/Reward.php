<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reward extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rewards';

    protected $fillable = [
        'name',
        'description',
        'cost',
        'stock',
        'image',
        'active',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'active' => 'boolean',
    ];

    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            });
    }

    public function hasStock(): bool
    {
        return is_null($this->stock) || $this->stock > 0;
    }

    public function decrementStock(): void
    {
        if (!is_null($this->stock)) {
            $this->decrement('stock');
        }
    }
}
