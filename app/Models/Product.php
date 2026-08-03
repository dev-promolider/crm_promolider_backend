<?php

namespace App\Models;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;
    protected $table = 'product';
    protected $guarded = ['id', 'price', 'status', 'created_at', 'updated_at'];

    /**
     * The payments that belong to the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class)->withPivot('quantity');;
    }
}
