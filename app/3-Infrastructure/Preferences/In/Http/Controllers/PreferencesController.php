<?php

namespace Promolider\Infrastructure\Preferences\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Promolider\Application\Preferences\UseCases\SavePreferencesUseCase;
use Promolider\Application\Preferences\UseCases\DeletePreferencesUseCase;
use Promolider\Application\Preferences\UseCases\GetPreferencesUseCase;
use Exception;

class PreferencesController extends Controller
{
    private SavePreferencesUseCase $savePreferencesUseCase;
    private DeletePreferencesUseCase $deletePreferencesUseCase;
    private GetPreferencesUseCase $getPreferencesUseCase;

    public function __construct(
        SavePreferencesUseCase $savePreferencesUseCase,
        DeletePreferencesUseCase $deletePreferencesUseCase,
        GetPreferencesUseCase $getPreferencesUseCase
    ) {
        $this->savePreferencesUseCase = $savePreferencesUseCase;
        $this->deletePreferencesUseCase = $deletePreferencesUseCase;
        $this->getPreferencesUseCase = $getPreferencesUseCase;
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $categoryIds = $request->input('categorys', []);
            $this->savePreferencesUseCase->execute($request->user()->id, $categoryIds);

            return response()->json([
                'success' => true,
                'message' => 'Categorías registradas correctamente',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteUserPreferences(Request $request): JsonResponse
    {
        try {
            $categoryIds = $request->input('categorys', []);
            $this->deletePreferencesUseCase->execute($request->user()->id, $categoryIds);

            return response()->json([
                'success' => true,
                'message' => 'Categorías eliminadas correctamente',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function myPreferences(Request $request): JsonResponse
    {
        try {
            $preferences = $this->getPreferencesUseCase->execute($request->user()->id);

            return response()->json([
                'data' => $preferences
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener preferencias'
            ], 500);
        }
    }
}
