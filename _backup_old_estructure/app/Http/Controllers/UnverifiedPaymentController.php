<?php

namespace App\Http\Controllers;

use App\Models\UnverifiedPayment;
use Illuminate\Http\Request;

class UnverifiedPaymentController extends Controller
{
    public function create(Request $request){
        $payment = new UnverifiedPayment();
        $payment->user_id = $request->user_id;
        $payment->openpay_order_id = $request->openpay_order_id;
        $payment->product_id = $request->product_id;
        $payment->product_detail = $request->product_detail;
        $payment->product_price = $request->product_price;
        $payment->product_name = $request->product_name;
        $payment->save();
    }
}
