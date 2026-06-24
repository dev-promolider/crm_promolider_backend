<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnverifiedUser;
use Illuminate\Support\Facades\Hash;

class UnverifiedUserController extends Controller
{
    public function create(Request $request){
        $enc_pass = Hash::make($request->password);
        $data = [
            'id_referrer_sponsor' => $request->id_referrer_sponsor,
            'username' => $request->username,
            'password' => $enc_pass,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'name' => $request->name,
            'last_name' => $request->last_name,
            'biography' => $request->biography,
            'phone' => $request->phone,
            'date_birth' => $request->date_birth,
            'id_document_type' => $request->id_document_type,
            'nro_document' => $request->nro_document,
            'id_country' => $request->id_country,
            'id_account_type' => $request->id_account_type,
            'purchase_number' => $request->purchase_number,
            'payment_method_id' => $request->payment_method_id,
            'payment_method' => $request->payment_method,
            'operation_number' => $request->operation_number,
            'openpay' => true,
        ];
        $user = new UnverifiedUser();
        $user->username = $request->username;
        $user->password = $enc_pass;
        $user->openpay_order_id = $request->order_id;
        $data = json_encode($data);
        $user->data = $data;
        $user->save();
        return $user;
    }
}
