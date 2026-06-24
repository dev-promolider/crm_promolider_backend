<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CertificatesClassroomRequest extends FormRequest
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
            'signature' => ['required','nullable','image','mimes:jpeg,png,jpg']
        ];
    }

    public function messages()
    {
        return [
            'signature.required' => 'Ingrese una imagen valida',
            'signature.image' => 'Ingrese una imagen valida con extensión jpeg,png,jpg',
            'signature.mimes' => 'Ingrese una imagen válida con extensión jpeg,png,jpg'
        ];
    }
}
