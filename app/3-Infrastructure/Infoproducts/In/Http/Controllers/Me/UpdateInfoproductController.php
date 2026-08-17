<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Me;

use Illuminate\Http\Request;
use Promolider\Application\Infoproducts\UseCases\UpdateInfoproductUseCase;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class UpdateInfoproductController extends BaseController
{
    public function __construct(
        private UpdateInfoproductUseCase $updateInfoproductUseCase
    ) {}

    public function __invoke(Request $request, int $id)
    {
        try {
            $user = $request->user();
            $data = $request->except(['file', 'file_video']);
            $coverFile = $request->file('file');
            $promoFile = $request->file('file_video');
            
            $result = $this->updateInfoproductUseCase->execute($id, $data, $user, $coverFile, $promoFile);

            return response()->json([
                'data' => [
                    'status' => 'ok',
                    'message' => $result['message']
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en UpdateInfoproductController: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
