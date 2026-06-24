<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    /**
     * Obtiene todas las categorías
     */
    public function getAll()
    {
        return Category::select('id', 'name', 'icon')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtiene todas las categorías (formato JSON para APIs)
     */
    public function getAllForApi()
    {
        try {
            $categories = $this->getAll();

            return [
                'success' => true,
                'data' => $categories,
                'message' => 'Categorías obtenidas correctamente'
            ];
        } catch (\Throwable $th) {
            Log::error('Error al obtener categorías', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al obtener las categorías',
                'error' => $th->getMessage(),
            ];
        }
    }

    /**
     * Crea una nueva categoría
     */
    public function create(array $data)
    {
        try {
            $category = Category::create([
                'name' => $data['name'],
                'icon' => $data['icon'] ?? null,
            ]);

            return [
                'success' => true,
                'data' => $category,
                'message' => 'Categoría creada correctamente'
            ];
        } catch (\Throwable $th) {
            Log::error('Error al crear categoría', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al crear la categoría',
                'error' => $th->getMessage(),
            ];
        }
    }

    /**
     * Actualiza una categoría existente
     */
    public function update($id, array $data)
    {
        try {
            $category = Category::findOrFail($id);
            $category->update([
                'name' => $data['name'],
                'icon' => $data['icon'] ?? $category->icon,
            ]);

            return [
                'success' => true,
                'data' => $category,
                'message' => 'Categoría actualizada correctamente'
            ];
        } catch (\Throwable $th) {
            Log::error('Error al actualizar categoría', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar la categoría',
                'error' => $th->getMessage(),
            ];
        }
    }

    /**
     * Elimina una categoría
     */
    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return [
                'success' => true,
                'message' => 'Categoría eliminada correctamente'
            ];
        } catch (\Throwable $th) {
            Log::error('Error al eliminar categoría', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar la categoría',
                'error' => $th->getMessage(),
            ];
        }
    }
}
