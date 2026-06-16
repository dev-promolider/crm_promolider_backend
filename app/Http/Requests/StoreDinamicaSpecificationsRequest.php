<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDinamicaSpecificationsRequest extends FormRequest
{
    public function authorize()
    {
        return true; // La autorización se maneja en el servicio
    }

    public function rules()
    {
        return [
            'id' => 'nullable|exists:dinamicas,id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'modoInscripcion' => 'required|in:tiempo',
            'tiempoInscripcion' => 'required|integer|min:1',
            'maxParticipantes' => 'nullable|integer|min:1',
            'mostrarInscritos' => 'boolean',
            'tipoPremio' => 'required|in:unico',
            'maxGanadores' => 'required|integer|in:1',
            'premios' => 'required|array|size:1',
            'premios.*.nombre' => 'required|string|max:255',
            'premios.*.tipo' => 'required|string|max:50',
            'premios.*.stock' => 'required|integer|min:1',
            'premios.*.peso' => 'required|integer|min:1',
            'premios.*.limiteUsuario' => 'nullable|integer|min:0',
            'premios.*.vigenciaInicio' => 'nullable|date',
            'premios.*.vigenciaFin' => 'nullable|date|after_or_equal:premios.*.vigenciaInicio',
            'premios.*.claimUrl' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la dinámica es obligatorio',
            'modoInscripcion.required' => 'Debe seleccionar un modo de inscripción',
            'tipoPremio.required' => 'El tipo de premio es obligatorio',
            'tipoPremio.in' => 'Solo se permite registrar un premio único por ruleta',
            'maxGanadores.required' => 'Debe especificar el número máximo de ganadores',
            'maxGanadores.in' => 'Solo se permite un ganador por ruleta',
            'premios.required' => 'Debe agregar exactamente un premio',
            'premios.size' => 'Solo puedes registrar un premio por ruleta',
            'premios.*.vigenciaFin.after_or_equal' => 'La fecha fin debe ser igual o posterior a la fecha inicio',
        ];
    }
}
