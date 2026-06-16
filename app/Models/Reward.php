<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reward extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Relación con los canjes realizados
     */
    public function redemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Scope para premios activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para premios disponibles (activos y con stock)
     */
    public function scopeAvailable($query)
    {
        return $query->where('active', true)
            ->where(function ($q) {
                $q->whereNull('stock')
                  ->orWhere('stock', '>', 0);
            });
    }

    /**
     * Verificar si el premio tiene stock disponible
     */
    public function hasStock(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }

    /**
     * Decrementar el stock
     */
    public function decrementStock(): void
    {
        if ($this->stock !== null) {
            $this->decrement('stock');
        }
    }
}