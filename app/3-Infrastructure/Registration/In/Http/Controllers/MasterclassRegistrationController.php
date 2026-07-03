<?php
namespace Promolider\Infrastructure\Registration\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Registration\UseCases\RegisterForMasterclassUseCase;

class MasterclassRegistrationController extends Controller
{
    public function __construct(
        private RegisterForMasterclassUseCase $registerForMasterclassUseCase
    ) {}

    public function register(Request $request)
    {
        $userId = $request->user()->id ?? $request->input('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
        }

        $this->registerForMasterclassUseCase->execute($userId);

        return response()->json([
            'success' => true,
            'message' => 'Registrado para la masterclass correctamente'
        ]);
    }
}
