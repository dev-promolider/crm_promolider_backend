<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTriviaDinamicaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dinamicaId' => 'nullable|integer|exists:dinamicas,id',
            'registrationConfig' => 'nullable|array',
            'registrationConfig.participantsLimit' => 'nullable|integer|min:1',
            'registrationConfig.timeLimitMinutes' => 'nullable|integer|min:1|max:1440',
            'registrationConfig.closingDateTime' => 'nullable|date',
            'registrationConfig.closingTime' => 'nullable|date_format:H:i',

            'triviaConfig' => 'required|array',
            'triviaConfig.name' => 'required|string|max:255',
            'triviaConfig.description' => 'nullable|string',
            'triviaConfig.slug' => 'nullable|string|max:255',
            'triviaConfig.pointsMin' => 'required|integer|min:0',
            'triviaConfig.pointsMax' => 'required|integer|gte:triviaConfig.pointsMin',

            'gameBlocks' => 'required|array|min:1',
            'gameBlocks.*.title' => 'required|string|max:255',
            'gameBlocks.*.categoryId' => 'nullable|integer|exists:question_categories,id',
            'gameBlocks.*.order' => 'required|integer|min:1',
            'gameBlocks.*.isActive' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'triviaConfig.name.required' => 'El nombre de la trivia es obligatorio.',
            'triviaConfig.pointsMin.required' => 'Debes definir el puntaje mínimo.',
            'triviaConfig.pointsMax.gte' => 'El puntaje máximo debe ser mayor o igual que el mínimo.',
            'gameBlocks.required' => 'Agrega al menos un bloque de juego.',
            'gameBlocks.*.title.required' => 'Cada bloque necesita un título.',
            'gameBlocks.*.categoryId.exists' => 'Selecciona categorías válidas para los bloques.',
        ];
    }
}
