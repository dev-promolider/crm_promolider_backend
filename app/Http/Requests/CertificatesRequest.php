<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertificatesRequest extends FormRequest
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
            'name' => ['required', 'min:3'],
            'template' => ['required', 'min:10'], 
            'signature' => ['nullable','image','mimes:jpeg,png,jpg']
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Ingrese un nombre',
           
            'name.min' => 'El nombre debe contener al menos 3 caracteres',
            'template.required' => 'Ingrese una plantilla HTML',

            'template.min' => 'La plantilla debe tener minimo 10 caracteres',

            'signature.image' => 'Ingrese una imagen valida'
        ];
    }
}
