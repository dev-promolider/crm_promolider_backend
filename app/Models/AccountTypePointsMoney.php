<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountTypePointsMoney extends Model
{
    use HasFactory;
    
    protected $table = 'account_type_points_money';
    public $timestamps = false;

    protected $fillable = [
        'account_type_id',
        'points',
    ];
}
