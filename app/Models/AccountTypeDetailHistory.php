<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTypeDetailHistory extends Model
{
    use HasFactory;
    
    protected $table = 'account_type_detail_histories';
    public $timestamps = false;

    protected $fillable = [
        'account_type_id',
        'account_type_detail_id',
        'purchase_date',
        'expiration_date',
        'status',
    ];
}
