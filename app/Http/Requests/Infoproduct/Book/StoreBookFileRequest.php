<?php

namespace App\Http\Requests\Infoproduct\Book;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookFileRequest extends FormRequest
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
            'course_id' => 'required|exists:courses,id',
            'file' => 'required|file|mimes:pdf,epub,xls,xlsx,xlsm,xlsb,csv|max:256000', // 250MB
        ];
    }

    public function messages()
    {
        return [
            'course_id.required' => 'El libro es obligatorio.',
            'course_id.exists' => 'El libro especificado no existe.',
            'file.required' => 'Debes subir un archivo.',
            'file.file' => 'Debe subir un archivo válido.',
            'file.mimes' => 'El archivo debe ser un PDF, EPUB, o archivo de Excel.',
            'file.max' => 'El tamaño del archivo no debe exceder los 250MB.',
        ];
    }
}
