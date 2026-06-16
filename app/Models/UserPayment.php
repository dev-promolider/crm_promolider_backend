<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPayment extends Model
{
    use HasFactory;

    protected $table = "user_payments";
    // protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'id_payment',
        'authorizationCode',
        'errorCode',
        'idCommerce',
        'shippingCity',
        'txDateTime',
        'purchaseOperationNumber',
        'shippingAddress',
        'card_account_type',
        'answerMessage',
        'bank_description',
        'cuota',
        'paymentReferenceCode',
        'brand',
        'purchaseVerification',
        'IDTransaction',
        'errorMessage',
        'authorizationResult',
    ];
}
