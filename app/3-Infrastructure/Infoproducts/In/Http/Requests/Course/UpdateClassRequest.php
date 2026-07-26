<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $resourcesRemoved = $this->input('resourcesRemoved', []);

        /*
         * Permite recibir resourcesRemoved de estas dos formas:
         *
         * resourcesRemoved[]=1
         * resourcesRemoved[]=2
         *
         * O como JSON:
         *
         * resourcesRemoved="[1,2]"
         */
        if (is_string($resourcesRemoved)) {
            $decoded = json_decode($resourcesRemoved, true);

            $resourcesRemoved = is_array($decoded)
                ? $decoded
                : [];
        }

        $this->merge([
            'class_id' => (int) $this->route('classId'),
            'resourcesRemoved' => $resourcesRemoved,
        ]);
    }

    public function rules(): array
    {
        return [
            'class_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'title' => [
                'required',
                'string',
                'max:254',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'time' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'resources' => [
                'nullable',
                'array',
                'max:3',
            ],

            'resources.*' => [
                'file',
                'max:51200',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,zip,jpg,jpeg,png',
            ],

            'resourcesRemoved' => [
                'nullable',
                'array',
            ],

            'resourcesRemoved.*' => [
                'integer',
                'distinct',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'La clase es obligatoria.',
            'class_id.integer' => 'El identificador de la clase no es válido.',

            'title.required' => 'El título de la clase es obligatorio.',
            'title.string' => 'El título debe ser un texto.',
            'title.max' => 'El título no puede superar los 254 caracteres.',

            'description.string' => 'La descripción debe ser un texto.',
            'description.max' => 'La descripción es demasiado extensa.',

            'time.integer' => 'La duración debe ser un número entero.',
            'time.min' => 'La duración no puede ser negativa.',

            'resources.array' => 'Los recursos deben enviarse como una lista.',
            'resources.max' => 'Solo puedes agregar un máximo de 3 recursos.',

            'resources.*.file' => 'Cada recurso debe ser un archivo válido.',
            'resources.*.max' => 'Cada recurso debe pesar como máximo 50 MB.',
            'resources.*.mimes' => 'Uno de los recursos tiene un formato no permitido.',

            'resourcesRemoved.array' => 'Los recursos eliminados deben enviarse como una lista.',
            'resourcesRemoved.*.integer' => 'El identificador del recurso no es válido.',
            'resourcesRemoved.*.distinct' => 'Existen recursos eliminados repetidos.',
        ];
    }
}
