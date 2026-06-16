<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;

class QuestionItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:1000'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'time_limit' => ['nullable', 'integer', 'between:5,600'],
            'is_active' => ['sometimes', 'boolean'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*.text' => ['required', 'string', 'max:255'],
            'options.*.is_correct' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->all();

        if ($this->has('is_active')) {
            $payload['is_active'] = filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }

        if ($this->has('options')) {
            $payload['options'] = collect($this->input('options'))
                ->map(function ($option) {
                    return [
                        'text' => Arr::get($option, 'text'),
                        'is_correct' => filter_var(Arr::get($option, 'is_correct'), FILTER_VALIDATE_BOOLEAN) ?? false,
                    ];
                })
                ->values()
                ->toArray();
        }

        $this->replace($payload);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $options = collect($this->input('options', []));

            if ($options->count() < 2) {
                $validator->errors()->add('options', __('Debes capturar al menos dos opciones.'));
            }

            $correctCount = $options->filter(fn ($option) => (bool) ($option['is_correct'] ?? false))->count();
            if ($correctCount !== 1) {
                $validator->errors()->add('options', __('Debes marcar exactamente una opción correcta.'));
            }
        });
    }
}
