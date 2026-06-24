<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'product_type',
        'product_id',
        'amount',
        'description',
        'active',
        'usage_count'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function getProduct()
    {
        switch ($this->product_type) {
            case 'membership':
                return AccountType::find($this->product_id);
            case 'course':
                return Course::find($this->product_id);
            case 'opc':
                return Product::where('name', 'opc')->first();
            default:
                return null;
        }
    }
}
