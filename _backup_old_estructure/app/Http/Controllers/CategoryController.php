<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Obtiene todas las categorías (API)
     */
    public function index()
    {
        $result = $this->categoryService->getAllForApi();
        $status = $result['success'] ? 200 : 500;
        
        return response()->json($result, $status);
    }

    public function store(Request $request){
        $result = $this->categoryService->create([
            'name' => $request->name,
            'icon' => $request->icon,
        ]);

        $status = $result['success'] ? 200 : 500;
        return response()->json($result, $status);
    }

    /**
     * Actualiza una categoría
     */
    public function update(Request $request){
        $result = $this->categoryService->update($request->id, [
            'name' => $request->name,
            'icon' => $request->icon,
        ]);

        $status = $result['success'] ? 200 : 500;
        return response()->json($result, $status);
    }

    /**
     * Elimina una categoría
     */
    public function delete($id){
        $result = $this->categoryService->delete($id);
        
        $status = $result['success'] ? 200 : 500;
        return response()->json($result, $status);
    }
}
