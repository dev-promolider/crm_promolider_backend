<?php
namespace Promolider\Infrastructure\Registration\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Registration\UseCases\RegisterForMinicourseUseCase;

class MinicourseRegistrationController extends Controller
{
    public function __construct(
        private RegisterForMinicourseUseCase $registerForMinicourseUseCase
    ) {}

    public function register(Request $request)
    {
        $userId = $request->user()->id ?? $request->input('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
        }

        $this->registerForMinicourseUseCase->execute($userId);

        return response()->json([
            'success' => true,
            'message' => 'Registrado para el minicurso correctamente'
        ]);
    }
}
