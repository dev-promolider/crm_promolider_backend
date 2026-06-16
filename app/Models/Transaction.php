<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = [
        'operation_number',
        'authorization',
        'operation_type',
        'transaction_type',
        'status',
        'conciliated',
        'creation_date',
        'operation_date',
        'description',
        'error_message',
        'order_id',
        'card',
        'due_date',
        'amount',
        'customer',
        'fee',
        'payment_method',
        'metadata',
        'currency',
        'method'
    ];

}