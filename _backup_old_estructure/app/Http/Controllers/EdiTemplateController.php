<?php

namespace App\Http\Controllers;

use App\Models\EditTemplate;
use Symfony\Component\HttpFoundation\Response; // Para que nos facilite el manejo de respuestas HTTP
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EdiTemplateController extends Controller
{
    
    public function index()
    {
        $ediTemplates = EditTemplate::all();
        if($ediTemplates->isEmpty()) {
            return response()->json(['message' => 'No se encontraron plantillas EDI'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['data' => $ediTemplates], Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $user = Auth::user(); // Obtener el usuario autenticado
        \Log::info('Iniciando registro de plantilla personalizada', ['user_id' => $user->id]);

        try {
            $validatedData = $request->validate([
                'template_id' => 'required|integer|exists:template,id',
                'title' => 'required|string|max:255',
                'content_html' => 'required|string',
                'edited_fields' => 'nullable|string',
                'status' => 'required|in:draft,published',
            ]);

            // Asignar el user_id directamente
            $validatedData['user_id'] = $user->id;

            $editTemplate = EditTemplate::create($validatedData);

            $editTemplate->load(['user', 'template']);

            return response()->json([
                'message' => 'Plantilla personalizada creada exitosamente',
                'data' => $editTemplate
            ], Response::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            \Log::error('Error al crear plantilla personalizada: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor al crear la plantilla personalizada'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        $ediTemplate = EditTemplate::find($id);

        if (!$ediTemplate) {
            return response()->json(['message' => 'Plantilla EDI no encontrada'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $ediTemplate], Response::HTTP_OK);
    }

    public function update(Request $request, EditTemplate $editemplate)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'content_html' => 'required|string',
                'edited_fields' => 'nullable|string', // Cambiado a string
                'status' => 'required|in:draft,published',
            ], [
                'title.required' => 'El campo título es obligatorio.',
                'title.string' => 'El campo título debe ser una cadena de texto.',
                'title.max' => 'El campo título no puede exceder los 255 caracteres.',
                'content_html.required' => 'El campo content_html es obligatorio.',
                'content_html.string' => 'El campo content_html debe ser una cadena de texto.',
                'edited_fields.string' => 'El campo edited_fields debe ser una cadena de texto JSON.',
                'status.required' => 'El campo status es obligatorio.',
                'status.in' => 'El campo status debe ser: draft o published.',
            ]);
        
            $editemplate->update($validatedData);
            
            // Cargar las relaciones
            $editemplate->load(['user', 'template']);
            
            return response()->json([
                'message' => 'Plantilla personalizada actualizada exitosamente',
                'data' => $editemplate
            ], Response::HTTP_OK);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
            
        } catch (\Exception $e) {
            \Log::error('Error al actualizar plantilla personalizada: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error interno del servidor al actualizar la plantilla personalizada'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUserTemplates($userId)
    {
        try {
            $userTemplates = EditTemplate::where('user_id', $userId)
                ->with(['template'])
                ->orderBy('updated_at', 'desc')
                ->get();

            if ($userTemplates->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron plantillas personalizadas para este usuario'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'data' => $userTemplates
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            \Log::error('Error al obtener plantillas del usuario: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error interno del servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function destroy(EditTemplate $editemplate)
    {
        if($editemplate->delete()){
            return response()->json(['message' => 'Plantilla EDI eliminada correctamente'], Response::HTTP_OK);
        }
        return response()->json(['message' => 'Error al eliminar la plantilla EDI'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    // Nueva función para mostrar página pública
    public function showPublicPage($slug)
    {
        try {
            $page = EditTemplate::where('slug', $slug)
                ->where('status', 'published')
                ->with(['user', 'template'])
                ->first();

            if (!$page) {
                abort(404, 'Página no encontrada');
            }

            // Retornar la vista con el contenido HTML
            return response($page->content_html)
                ->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            \Log::error('Error al mostrar página pública: ' . $e->getMessage());
            abort(500, 'Error interno del servidor');
        }
    }

    // Método auxiliar para generar slug único
    private function generateUniqueSlug($title, $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        $query = EditTemplate::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            
            $query = EditTemplate::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
