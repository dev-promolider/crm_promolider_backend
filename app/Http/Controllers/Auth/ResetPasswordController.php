<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
  // protected $redirectTo = RouteServiceProvider::HOME;

  /**
   * Redirigir al login tras resetear la contraseña, mostrando mensaje de éxito.
   */
  /**
   * Where to redirect users after resetting their password.
   *
   * @var string
   */
  // protected $redirectTo = RouteServiceProvider::HOME;

  /**
   * Sobrescribe el comportamiento por defecto para NO iniciar sesión tras el reseteo.
   */
  protected function resetPassword($user, $password)
  {
      $user->password = bcrypt($password);
      $user->setRememberToken(Str::random(60));
      $user->save();
      // NO llamar a $this->guard()->login($user);
  }

  /**
   * Redirigir al login tras resetear la contraseña, mostrando mensaje de éxito.
   */
  protected function sendResetResponse(Request $request, $response)
  {
      return redirect()->route('login')->with('success', '¡Tu contraseña ha sido restablecida correctamente! Ahora puedes iniciar sesión con tu nueva contraseña.');
  }
}