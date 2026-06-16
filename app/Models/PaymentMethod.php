<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $table = 'payment_method';
    protected $fillable = ['name','status'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'payment_method_user');
    }
}
