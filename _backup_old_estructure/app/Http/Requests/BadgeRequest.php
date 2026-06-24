<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BadgeRequest extends FormRequest
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
            'description' => ['required', 'min:3'],
            'logo' => ['nullable','image','mimes:jpeg,png,jpg']
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'Ingrese una descripción',
           
            'description.min' => 'La descripción debe contener al menos 3 caracteres',
            'logo.image' => 'Ingrese una imagen valida'
        ];
    }
}
