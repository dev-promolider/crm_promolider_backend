<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username'=>'required',
            'password'=>'required',
            // 'g-recaptcha-response' => 'required|recaptcha',
        ];
    }

    public function messages(){
        return [
            'username.required' => 'Este campo es obligatorio',
            'password.required' => 'Este campo es obligatorio',
            // 'g-recaptcha-response.required' => 'Debes validar que no eres un robot',
            // 'g-recaptcha-response.captcha' => '¡Error de CAPTCHA! inténtelo de nuevo más tarde o comuníquese con el administrador del sitio.',
        ];
    }
}
