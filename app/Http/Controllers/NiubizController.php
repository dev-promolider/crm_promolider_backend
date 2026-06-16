<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\User;
use Illuminate\Http\Request;

class NiubizController extends Controller
{
    public static function createNiubizToken()
    {
        $api = "https://apisandbox.vnforappstest.com/api.security/v1/security";
        $username = 'integraciones@niubiz.com.pe';
        $password = '_7z3@8fF';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  $api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($username . ':' . $password)
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

        ));
        $response = curl_exec($curl);
        return $response;
    }

    public function index(Request $request)
    {
        if (session()->missing('body')) {
            return redirect()->route('login-form')->withWarning("Proceda a registrarse");
        }

        $ip =   $request->ip();

        $user_info = session()->get("body");
        $purchase_number = $user_info["purchase_number"];

        $id_membership = session()->get("body")["id_account_type"];

        $info_membership = AccountType::where('id', $id_membership)->get()->first();
        $price_total = $info_membership->price + ($info_membership->price * ($info_membership->iva / 100));
        $info_membership->total =  sprintf('%.2f', $price_total);
        $info_membership->price = sprintf('%.2f', $info_membership->price);
        $sponsor = User::select('id', 'name', 'last_name')->where('id', $user_info["id_referrer_sponsor"])->get()->first();
        return view('niubiz.payment', compact('ip', 'user_info', 'info_membership', 'sponsor', 'purchase_number'));
    }

    public function getMembershipUsefulData($id)
    {
        $info_membership = AccountType::where('id', $id)->get()->first();
        $membership = $info_membership->account;
        $total = sprintf('%.2f', $info_membership->price + ($info_membership->price * ($info_membership->iva / 100)));;
        return compact('total', 'membership');
    }

    // 4 Step and the last one authorize the payment
    public function authorizeTransaction(Request $request)
    {
        $transaction_token = $request->transactionToken;
        $merchant_id = '456879853';
        $api = "https://apisandbox.vnforappstest.com/api.authorization/v3/authorization/ecommerce/$merchant_id}";

        $data = session()->get("body");

        $membership_data = $this->getMembershipUsefulData(session()->get("body")["id_account_type"]);
        $membership = $membership_data["membership"];

        $purchase_number = session()->get("body")["purchase_number"];

        $purchase_data = array(
            "channel" => $request->channel,
            "captureType" => "manual",
            'countable' => 'true',
            "order" => array(
                "tokenId" => $transaction_token,
                "purchaseNumber" => $purchase_number,
                "amount" => $membership_data["total"],
                "currency" => "USD"
            ),
        );

        $access_token = $this->createNiubizToken();

        return view('niubiz.invoice', compact('purchase_data', 'access_token', 'data', 'membership', 'purchase_number'));
    }

    public function process(Request $request)
    {
        app(UserController::class)->Create($request->order["purchaseNumber"]);
    }
}
