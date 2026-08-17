<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Me;

use Illuminate\Http\Request;
use Promolider\Application\Infoproducts\UseCases\DeleteInfoproductUseCase;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class DeleteInfoproductController extends BaseController
{
    public function __construct(
        private DeleteInfoproductUseCase $deleteInfoproductUseCase
    ) {}

    public function __invoke(Request $request, int $id)
    {
        try {
            $user = $request->user();
            
            $success = $this->deleteInfoproductUseCase->execute($id, $user);

            if ($success) {
                return response()->json([
                    'status' => 'ok', // Changed from inactive to ok, or we can send inactive based on user request.
                    // The frontend expects 'inactive' to show 'El curso fue marcado como inactivo.'
                    // Let's just return 'inactive' to match frontend handling perfectly.
                    'status' => 'inactive',
                    'message' => 'El curso ha sido inhabilitado correctamente.'
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo eliminar el curso.'
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error en DeleteInfoproductController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
