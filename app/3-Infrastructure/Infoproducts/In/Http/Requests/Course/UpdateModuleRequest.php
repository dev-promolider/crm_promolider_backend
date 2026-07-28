<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:254',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del módulo es obligatorio.',
            'name.string' => 'El nombre del módulo debe ser un texto.',
            'name.max' => 'El nombre del módulo no puede superar los 254 caracteres.',
        ];
    }
}
