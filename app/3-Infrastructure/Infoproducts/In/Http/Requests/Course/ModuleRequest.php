<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

final class ModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                'integer',
                'exists:courses,id',
            ],
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
            'course_id.required' => 'El curso es obligatorio.',
            'course_id.exists' => 'El curso seleccionado no existe.',
            'name.required' => 'El nombre del módulo es obligatorio.',
            'name.max' => 'El nombre no puede superar los 254 caracteres.',
        ];
    }
}
