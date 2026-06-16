<?php

namespace App\Http\Controllers\Templates;

use App\Http\Controllers\Controller;
use App\Models\Template;  // Modelo MySQL correcto
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Obtener todas las plantillas
     */
    public function index()
    {
        try {
            $templates = Template::all();
            return response()->json([
                'data' => $templates,
                'message' => 'Templates retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener plantillas',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
      
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'content' => 'required|string',
        ]);

        
        $template = Template::create([
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'price' => $request->input('price'),
            'content' => $request->input('content'),
        ]);

        
        return response()->json($template, 201);
    }

    public function show($id){

        return response()->json(Template::findOrFail($id),200);
    }

    public function update(Request $request, $id)
    {
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'content' => 'sometimes|required|string', 
        ]);

       
        $template = Template::find($id);
        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        
        $template->update($request->all());

        
        return response()->json($template, 200);
    }

    public function delete($id)
    {
        
        $template = Template::find($id);
        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

       
        $template->delete();

        return response()->json(['message' => 'Template deleted successfully'], 200);
    }






}
