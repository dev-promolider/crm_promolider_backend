<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class WalletPaymetMethod extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'wallet_payment_method_user');
    }
}
