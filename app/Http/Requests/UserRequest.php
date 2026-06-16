<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'username' => ['required','unique:users','min:3','max:50'],
            'password' => ['required','min:5','confirmed'],
            'name' => ['required','min:2','max:50'],
            'last_name' => ['required','min:3','max:50'],
            'phone' => ['required','integer','unique:users'],
            'date_birth' => ['required','date','before:' . Carbon::now()->subYears(18)],
            'email' => ['required','unique:users'],
            'id_referrer_sponsor' => ['required','exists:users,id'],
            'id_country' => ['required','exists:country,id'],
            'id_document_type' => ['required','exists:document_type,id'],
            'id_account_type' => ['required','numeric','min:1','not_in:1','exists:account_type,id'],
            // 'nro_document' => ['required','min:10000000','integer','unique:users'],
            'nro_document' => ['required','unique:users'],
            'biography' => ['required'],
            'user_type' => ['required','exists:roles,name'],
            'payment_method' => ['required'],
            'operation_number' => ['unique:payments,operation_number'],
        ];
    }

    public function messages(){
        return [
            'username.required' => 'El Usuario no debe estar vacío',
            'username.unique' => 'Este usuario ya está registrado',
            'username.min' => 'El usuario debe contener al menos 3 letras',
            'username.max' => 'El usuario debe contener un maximo de 50 letras',
            'password.required' => 'El Contraseña no debe estar vacío',
            'password.min' => 'El Contraseña debe contener al menos 5 letras',
            'password.confirmed' => 'La contraseña no coincide',
            'name.required' => 'El Nombre de debe estar vacío',
            'name.min' => 'El Nombre debe contener al menos 3 letras',
            'name.max' => 'El Nombre debe contener un maximo 50 letras',
            'last_name.required' => 'El Apellidos no debe estar vacío',
            'last_name.min' => 'El Apellidos debe contener al menos 3 letras',
            'last_name.max' => 'El Apellidos debe contener un maximo 50 letras',
            'phone.required' => 'El Teléfono no debe estar vacío',
            'phone.min' => 'El Teléfono debe contener 9 dígitos + el prefijo del país. ejemplo: 51987654321',
            'phone.integer' => 'El Teléfono debe ser numerico',
            'phone.unique' => 'El Teléfono ya se fue registrado',
            'date_birth.required' => 'El Fecha de nacimiento no debe estar vacío',
            'date_birth.date' => 'El Fecha de nacimiento no debe estar vacío',
            'date_birth.before' => 'Debe ser mayor de edad',
            'email.required' => 'El Correo electrónico no debe estar vacío',
            'email.email' => 'El Correo electrónico debe tener el formato correcto',
            'email.unique' => 'El correo electrónico ya fue registrado',
            'id_referrer_sponsor.exists' => 'No se seleccionó un sponsor válido',
            'id_country.required' => 'El  País no debe estar vacío',
            'id_country.exists' => 'El  País no es válido',
            'id_document_type.required' => 'Seleccione un tipo de documento',
            'id_document_type.exists' => 'Seleccione un tipo de documento',
            'id_account_type.required' => 'Seleccione un tipo de cuenta',
            'id_account_type.exists' => 'Seleccione un tipo de cuenta',
            'id_account_type.min' => 'Seleccione un tipo de cuenta',
            'nro_document.required' => 'El Número de documento es obligatorio',
            'nro_document.integer' => 'El Número de documento solo debe contener números',
            'nro_document.min' => 'El Número de documento debe contener al menos 8 caracteres',
            'nro_document.unique' => 'El Número de documento ya ha sido registrado',
            'biography.required' => 'El biografía no debe estar vacío',
            'user_type.required' => 'Seleccione un tipo de usuario',
            'user_type.exists' => 'Seleccione un tipo de usuario',
            'payment_method.required' => 'Seleccione un método de pago',
            'operation_number.unique' => 'Este número de operación fue registrado anteriormente',
        ];
    }
}
