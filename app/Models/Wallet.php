<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'wallet';
    protected $guarded = ['id', 'user_id', 'balance', 'total_redeemed', 'created_at', 'updated_at'];
}
