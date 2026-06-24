<?php

namespace App\Http\Requests\Infoproduct\Book;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookObservationRequest extends FormRequest
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
            'observations' => [
                Rule::requiredIf(fn () => $this->input('status') === 'disapproved'),
                'nullable',
                'string',
                'max:1500'
            ],
            'status' => 'in:approved,disapproved'
        ];
    }

    public function messages()
    {
        return [
            'observations.string' => 'Las observaciones deben ser una cadena de texto.',
            'observations.max' => 'Las observaciones no pueden exceder los 1500 caracteres.',
            'status.in' => 'El estado debe ser "approved" o "disapproved".',
        ];
    }
}
