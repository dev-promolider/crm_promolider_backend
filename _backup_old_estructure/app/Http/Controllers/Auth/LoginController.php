<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\Notifications;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;


    public function __construct()
    {
        $this->middleware('guest')->except('logout', 'redirectToLoginWithMessage', 'showLoginForm');
    }

    public function showLoginForm()
    {
        if (!Auth::user()) {
            $pageConfigs = [
                'bodyClass' => "bg-full-screen-image",
                'blankPage' => true
            ];
            return view('/auth/login', [
                'pageConfigs' => $pageConfigs
            ]);
        } else {
            $pageConfigs = ['pageHeader' => false];

            return view('/content/dashboard/dashboard-ecommerce', ['pageConfigs' => $pageConfigs]);
        }
    }

    public function redirectToLoginWithMessage(Request $request)
    {
        return redirect()->route('login-form')->with('warning', $request->message);
    }

    public function username()
    {
        return 'username';
    }

    protected function login2(LoginRequest $request)
    {
        $userTimezone = $request->input('user_timezone');

        $verify_login = 0;
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
                $verify_login = 1;
            }
        }

        if ($verify_login == 1) {
            $user = User::where('username', $request->username)->first();
            $request_status = $user->request;

            if ($request_status == 2) {

                if ($user) {
                    $user->update([
                        'timezone' => $userTimezone
                    ]);
                }

                $expiration_date = $user->expiration_date;

                $date = Carbon::parse($expiration_date);
                $now1 = Carbon::now();

                $diff = $date->diffInDays($now1);

                if ($diff == 7) {
                    $title = 'Su estado esta por pasar a inactivo!!!';
                    $body = "Le queda una semana para poder hacer la recompra";
                    $this->notification($user->id, $title, $body);
                }

                $now = Carbon::now();
                $expire_active_field = $now->gt($expiration_date);

                return $this->sendLoginResponse($request);
            } else if ($request_status == 1) {
                $this->logout($request);
                $msg = 'Solicitud de acceso pendiente';
                return redirect()->route('login-form')->with('warning', $msg);
            } else if ($request_status == 3) {
                $this->logout($request);
                $msg = 'Su solicitud ha sido rechazada';
                return redirect()->route('login-form')->with('warning', $msg);
            }
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function notification($id_user, $title, $body)
    {
        try {
            DB::beginTransaction();
            $notification = new Notifications();
            $notification->id_generator = $id_user;
            $notification->id_receiver =  $id_user;
            $notification->title = $title;
            $notification->body = $body;
            $notification->type = 3;
            $notification->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('Usuario o contraseña incorrectos')],
        ]);
    }
}
