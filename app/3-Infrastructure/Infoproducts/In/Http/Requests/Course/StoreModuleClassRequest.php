<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

final class StoreModuleClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo comprueba autenticación.
        // La pertenencia del módulo puede verificarse en el UseCase.
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'module_id' => (int) $this->route('moduleId'),
        ]);
    }

    public function rules(): array
    {
        return [
            'module_id' => [
                'bail', 
                'required',
                'integer',
                'exists:modules,id',
            ],

            'title' => [
                'bail',
                'required',
                'string',
                'max:254',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'resources' => [
                'nullable',
                'array',
                'max:3',
            ],

            'resources.*' => [
                'bail',
                'file',
                'max:51200',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,zip,jpg,jpeg,png',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'El módulo es obligatorio.',
            'module_id.integer' => 'El identificador del módulo no es válido.',
            'module_id.exists' => 'El módulo seleccionado no existe.',

            'title.required' => 'El título de la clase es obligatorio.',
            'title.string' => 'El título debe ser un texto.',
            'title.max' => 'El título no puede superar los 254 caracteres.',

            'description.string' => 'La descripción debe ser un texto.',
            'description.max' => 'La descripción es demasiado extensa.',

            'resources.array' => 'Los recursos deben enviarse como una lista de archivos.',
            'resources.max' => 'Solo puedes subir un máximo de 3 recursos.',

            'resources.*.file' => 'Cada recurso debe ser un archivo válido.',
            'resources.*.max' => 'Cada recurso debe pesar como máximo 50 MB.',
            'resources.*.mimes' => 'Uno de los recursos tiene un formato no permitido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'module_id' => 'módulo',
            'title' => 'título',
            'description' => 'descripción',
            'resources' => 'recursos',
            'resources.*' => 'recurso',
        ];
    }
}
