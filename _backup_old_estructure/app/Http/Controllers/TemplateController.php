<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response; // Para que nos facilite el manejo de respuestas HTTP
use App\Models\Template;
use Illuminate\Validation\ValidationException;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::all();
        
        if($templates->isEmpty()) {
            return response()->json(['message' => 'No se encontraron plantillas'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $templates], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'thumbnail' => 'nullable|string|max:500',
                'content_html' => 'required|string',
                'membresia' => 'required|in:free,premium,pro',
            ],[
                'name.required' => 'El campo nombre es obligatorio.',
                'name.string' => 'El campo nombre debe ser una cadena de texto.',
                'name.max' => 'El campo nombre no puede exceder los 255 caracteres.',
                'description.required' => 'El campo descripción es obligatorio.',
                'description.string' => 'El campo descripción debe ser una cadena de texto.',
                'description.max' => 'El campo descripción no puede exceder los 1000 caracteres.',
                'thumbnail.string' => 'El campo thumbnail debe ser una cadena de texto.',
                'thumbnail.max' => 'El campo thumbnail no puede exceder los 500 caracteres.',
                'content_html.required' => 'El campo content_html es obligatorio.',
                'content_html.string' => 'El campo content_html debe ser una cadena de texto.',
                'membresia.required' => 'El campo membresía es obligatorio.',
                'membresia.in' => 'El campo membresía debe ser: free, premium o pro.',
            ]);

            $template = Template::create($validatedData);
            return response()->json(['data' => $template], Response::HTTP_CREATED);
        
        }catch(ValidationException $e){
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(Template $template)
    {
        if(!$template) {
            return response()->json(['message' => 'Plantilla no encontrada'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $template], Response::HTTP_OK);
    }

    public function update(Request $request, Template $template)
    {
        try{
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'thumbnail' => 'nullable|string|max:500',
                'content_html' => 'required|string',
                'membresia' => 'required|in:free,premium,pro',
            ],[
                'name.required' => 'El campo nombre es obligatorio.',
                'name.string' => 'El campo nombre debe ser una cadena de texto.',
                'name.max' => 'El campo nombre no puede exceder los 255 caracteres.',
                'description.required' => 'El campo descripción es obligatorio.',
                'description.string' => 'El campo descripción debe ser una cadena de texto.',
                'description.max' => 'El campo descripción no puede exceder los 1000 caracteres.',
                'thumbnail.string' => 'El campo thumbnail debe ser una cadena de texto.',
                'thumbnail.max' => 'El campo thumbnail no puede exceder los 500 caracteres.',
                'content_html.required' => 'El campo content_html es obligatorio.',
                'content_html.string' => 'El campo content_html debe ser una cadena de texto.',
                'membresia.required' => 'El campo membresía es obligatorio.',
                'membresia.in' => 'El campo membresía debe ser: free, premium o pro.',
            ]);

            $template->update($validatedData);
            return response()->json(['data' => $template], Response::HTTP_OK);

        }catch(ValidationException $e){
            return response()->json(['errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
    }


    public function destroy(Template $template)
    {
        if($template->delete()){
            return response()->json(['message' => 'Plantilla eliminada correctamente'], Response::HTTP_OK);
        } else {
            return response()->json(['message' => 'Error al eliminar la plantilla'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
