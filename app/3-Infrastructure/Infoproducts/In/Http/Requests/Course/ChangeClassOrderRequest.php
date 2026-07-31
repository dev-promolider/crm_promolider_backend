<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChangeClassOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $order = $this->input('order', []);

        // Mantiene compatibilidad con JSON.stringify(classes).
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
            ],

            'order.*.id' => [
                'required',
                'integer',
                'distinct',
                'exists:classes,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'El curso es obligatorio.',
            'id.exists' => 'El curso seleccionado no existe.',

            'order.required' => 'El orden de las clases es obligatorio.',
            'order.array' => 'El orden debe enviarse como una lista.',

            'order.*.id.required' => 'Cada clase debe tener un identificador.',
            'order.*.id.integer' => 'El identificador de la clase no es válido.',
            'order.*.id.distinct' => 'Existen clases repetidas.',
            'order.*.id.exists' => 'Una de las clases no existe.',
        ];
    }
}
