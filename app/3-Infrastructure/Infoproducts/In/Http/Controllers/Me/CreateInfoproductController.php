<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Me;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\CreateInfoproductUseCase;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateInfoproductController extends Controller
{
    public function __construct(
        private CreateInfoproductUseCase $createInfoproductUseCase
    ) {}

    public function __invoke(Request $request)
    {
        try {
            $user = $request->user();
            
            $data = $request->all();
            
            $coverFile = $request->hasFile('file') ? $request->file('file') : null;
            $promoFile = $request->hasFile('file_video') ? $request->file('file_video') : null;

            $result = $this->createInfoproductUseCase->execute($data, $user, $coverFile, $promoFile);

            return response()->json([
                'data' => $result,
                'message' => 'Infoproducto creado con éxito'
            ], 200);

        } catch (Throwable $th) {
            Log::error('Error creando infoproducto:', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);

            return response()->json([
                'data' => ['status' => 'error'],
                'message' => $th->getMessage()
            ], 422);
        }
    }
}
