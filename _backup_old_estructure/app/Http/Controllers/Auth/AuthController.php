<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UserRegisterRequest;
use App\Models\AccountType;
use App\Models\Classified;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserPayment;
use App\Models\Wallet;
use App\Traits\ResponseFormat;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use ResponseFormat, AuthenticatesUsers;

    public function store(UserRegisterRequest $request)
    {
        DB::beginTransaction();
        try {
            $tbRequest = $request->reserved10 == 5 ? 2 : 1;

            $user = User::create([
                'username' => $request->reserved1,
                'password' => Hash::make($request->reserved2),
                'name' => $request->shippingFirstName,
                'last_name' => $request->shippingLastName,
                'phone' => $request->reserved4,
                'date_birth' => $request->reserved5,
                'email' => $request->shippingEmail,
                'id_referrer_sponsor' => $request->reserved9,
                'id_country' => $request->reserved8,
                'id_document_type' => $request->reserved6,
                'id_account_type' => $request->reserved10,
                'nro_document' => $request->reserved7,
                'request' => $tbRequest,
                'expiration_date' => strtotime('+30 days')
            ]);
            $user_id = $user->id;
            /**
             * store payment
             */
            $payment = Payment::create([
                'user_id' => $user_id,
                'id_user_sponsor' => $request->reserved9,
                'amount' => $request->reserved13,
                'operation_number' => 0,
                'id_payment_method' => $request->reserved14
            ]);
            $id_payment = $payment->id;

            /**
             * store classification
             */
            if (Auth::check()) {
                $classified = '';
                $id_classified = '';
                if ($user->id_account_type != 5) {
                    if (auth()->user()->position == 1) {
                        $classified = Classified::create([
                            'user_id' => $user_id,
                            'id_user_sponsor' => auth()->user()->id,
                            'binary_sponsor' => 'test',
                            'position' => '0',
                            'classification' => 16,
                            'status' => '0',
                            'authorized' => '1',
                        ]);
                        $id_classified = $classified->id;
                    } else {
                        $classified = Classified::create([
                            'user_id' => $user_id,
                            'id_user_sponsor' => auth()->user()->id,
                            'binary_sponsor' => 'test',
                            'position' => '0',
                            'classification' => 16,
                            'status' => '0',
                            'authorized' => '1',
                        ]);
                        $id_classified = $classified->id;
                    }
                }
            }

            /**
             * store user_payment
             */
            UserPayment::create([
                'user_id' => $user_id,
                'id_payment' => $id_payment,
                'authorizationCode' => $request->authorizationCode ? $request->authorizationCode : '',
                'errorCode' => $request->errorCode ? $request->errorCode : '',
                'idCommerce' => $request->idCommerce ? $request->idCommerce : 0,
                'shippingCity' => $request->shippingCity,
                'txDateTime' => $request->txDateTime ? $request->txDateTime : '',
                'purchaseOperationNumber' => $request->purchaseOperationNumber ? $request->purchaseOperationNumber : 0,
                'shippingAddress' => $request->shippingAddress ? $request->shippingAddress : '',
                'card_account_type' => $request->card_account_type ? $request->card_account_type : '',
                'answerMessage' => $request->answerMessage ? $request->answerMessage : '',
                'bank_description' => $request->bank_description ? $request->bank_description : '',
                'cuota' => $request->cuota ? $request->cuota : '',
                'paymentReferenceCode' => $request->paymentReferenceCode ? $request->paymentReferenceCode : '',
                'brand' => $request->brand ? $request->brand : '',
                'purchaseVerification' => $request->purchaseVerification ? $request->purchaseVerification : '',
                'IDTransaction' => $request->IDTransaction ? $request->IDTransaction : '',
                'errorMessage' => $request->errorMessage ? $request->errorMessage : '',
                'authorizationResult' => $request->authorizationResult ? $request->authorizationResult : ''
            ]);

            //Generar Token
            $access_token = $user->createToken('authToken')->plainTextToken;

            DB::commit();
            return response([
                'user' => $user,
                'access_token' => $access_token,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function login(LoginRequest $request)
    {
        DB::beginTransaction();
        try {
            // $username = $request->username;
            // $password = $request->password;

            $login_credentials = [
                'username' => $request->username,
                'password' => $request->password,
            ];

            if (Auth::attempt($login_credentials)) {
                $user = User::where('username', $request->username)->first();
                if ($user->request == 2) {
                    $user_role = $user->getRoleNames();

                    $tokenResult = $user->createToken('authToken');
                    DB::commit();

                    return $this->responseOk(__('auth.correct_login'), [
                        'access_token' => $tokenResult->plainTextToken,
                        'token_type' => 'Bearer',
                        'user' => $user->toArray(),
                        'role' => $user_role,
                    ]);
                }
                if ($user->request == 1 || $user->request == 3){
                    return $this->responseForbidden();
                }
            } else {
                return $this->responseUnauthorized();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function loginApiForRedirect(Request $request)
    {
        DB::beginTransaction();
        try {

            $login_credentials = [
                'username' => $request->username,
                'password' => $request->password,
            ];

            $user = User::where("username", $request->username)->first();
            if ($request->password == $user->password) {
                $user = User::where('username', $request->username)->first();
                $user_role = $user->getRoleNames();

                $tokenResult = $user->createToken('authToken');
                DB::commit();

                return $this->responseOk(__('auth.correct_login'), [
                    'access_token' => $tokenResult->plainTextToken,
                    'token_type' => 'Bearer',
                    'user' => $user->toArray(),
                    'role' => $user_role,
                ]);
            } else {
                return $this->responseUnauthorized();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
