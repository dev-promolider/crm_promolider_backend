<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserLevelRequest extends FormRequest
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
            'experience_required' => ['required', 'integer', 'min:0'],
            'url_icon' => ['nullable','image','mimes:jpeg,png,jpg']
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'Ingrese una descripción',
           
            'description.min' => 'La descripción debe contener al menos 3 caracteres',
            'experience_required.required' => 'Ingrese la cantidad de experiencia',
           
            'experience_required.integer' => 'La experiencia debe ser numerica',
            'experience_required.min' => 'La experiencia debe ser minimo 0',

            'url_icon.image' => 'Ingrese una imagen valida'
        ];
    }
}
