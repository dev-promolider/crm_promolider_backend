<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTypeDetail extends Model
{
    use HasFactory;
    
    protected $table = 'account_type_details';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'purchase_date',
        'expiration_date',
        'status',
    ];
}
