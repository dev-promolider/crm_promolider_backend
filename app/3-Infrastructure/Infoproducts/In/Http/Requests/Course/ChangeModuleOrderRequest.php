<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChangeModuleOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $order = $this->input('order', []);

        // Compatibilidad con JSON.stringify(modules)
        if (is_string($order)) {
            $decoded = json_decode($order, true);

            $order = is_array($decoded)
                ? $decoded
                : [];
        }

        $this->merge([
            'order' => $order,
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                'exists:courses,id',
            ],

            'order' => [
                'required',
                'array',
                'min:1',
            ],

            'order.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:modules,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'El curso es obligatorio.',
            'id.integer' => 'El identificador del curso no es válido.',
            'id.exists' => 'El curso indicado no existe.',

            'order.required' => 'El orden de los módulos es obligatorio.',
            'order.array' => 'El orden debe enviarse como una lista.',
            'order.min' => 'Debe enviarse al menos un módulo.',

            'order.*.id.required' => 'Cada módulo debe tener un identificador.',
            'order.*.id.integer' => 'Uno de los identificadores no es válido.',
            'order.*.id.distinct' => 'Existen módulos repetidos.',
            'order.*.id.exists' => 'Uno de los módulos no existe.',
        ];
    }
}
