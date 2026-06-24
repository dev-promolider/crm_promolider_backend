<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenpayOrder extends Model
{
    use HasFactory;

    protected $table = 'openpay_order';

    protected $fillable = [
        'value'
    ];
}
