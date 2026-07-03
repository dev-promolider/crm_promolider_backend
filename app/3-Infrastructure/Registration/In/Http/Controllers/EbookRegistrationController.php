<?php
namespace Promolider\Infrastructure\Registration\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Registration\UseCases\RegisterForEbookUseCase;

class EbookRegistrationController extends Controller
{
    public function __construct(
        private RegisterForEbookUseCase $registerForEbookUseCase
    ) {}

    public function register(Request $request, $ebookId = null)
    {
        $userId = $request->user()->id ?? $request->input('user_id');
        
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
        }

        $this->registerForEbookUseCase->execute($userId, $ebookId);

        return response()->json([
            'success' => true,
            'message' => 'Registrado para el Ebook correctamente'
        ]);
    }
}
