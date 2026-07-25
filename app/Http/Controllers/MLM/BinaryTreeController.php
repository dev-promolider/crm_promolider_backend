<?php

namespace App\Http\Controllers\MLM;

use App\Http\Controllers\Controller;
use App\Services\MLM\BinaryTreeService;
use Illuminate\Http\Request;

class BinaryTreeController extends Controller
{
    protected $binaryTreeService;

    public function __construct(BinaryTreeService $binaryTreeService)
    {
        $this->binaryTreeService = $binaryTreeService;
    }

    /**
     * Obtiene el árbol binario completo desde la caché de Redis.
     */
    public function getFullTree(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'No autorizado. Solo los administradores pueden ver el árbol completo.'], 403);
        }

        $tree = $this->binaryTreeService->getGlobalTree();

        if (!$tree) {
            return response()->json(['message' => 'No se pudo generar el árbol binario'], 500);
        }

        return response()->json(['data' => $tree]);
    }

    /**
     * Obtiene el sub-árbol de un usuario específico.
     * Ideal para cargar la vista del dashboard del usuario actual.
     */
    public function getUserTree(Request $request, $userId = null)
    {
        // Forzamos a que el usuario SIEMPRE sea la raíz de su propia vista (Privacidad Top-Down)
        // Solo un Admin podría consultar el árbol de otro usuario pasando el $userId
        if ($userId && $request->user()->hasRole('Admin')) {
            $targetUserId = $userId;
        } else {
            $targetUserId = $request->user()->id;
        }

        $tree = $this->binaryTreeService->getSubTreeForUser($targetUserId);

        if (!$tree) {
            return response()->json(['message' => 'No se encontró el nodo del usuario en el árbol'], 404);
        }

        return response()->json(['data' => $tree]);
    }

    /**
     * Refresca la caché del árbol binario manualmente.
     * Útil cuando se inscribe alguien nuevo.
     */
    public function refreshTree(Request $request)
    {
        if (!$request->user()->hasRole('Admin')) {
            return response()->json(['message' => 'No autorizado. Solo los administradores pueden refrescar la caché manualmente.'], 403);
        }

        $tree = $this->binaryTreeService->buildTreeAndCache();

        return response()->json([
            'message' => 'Caché del árbol binario actualizada exitosamente',
            'data' => $tree
        ]);
    }
}
