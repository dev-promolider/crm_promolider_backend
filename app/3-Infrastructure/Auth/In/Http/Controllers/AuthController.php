<?php
namespace Promolider\Infrastructure\Auth\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Auth\UseCases\LoginUseCase;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        private LoginUseCase $loginUseCase
    ) {}

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        try {
            // Pasamos el control a la capa de Aplicación (UseCase)
            $result = $this->loginUseCase->execute(
                $request->username, 
                $request->password
            );
            
            return response()->json([
                'success' => true,
                'message' => __('auth.correct_login'),
                'data' => $result
            ], 200);

        } catch (Exception $e) {
            $code = $e->getCode();
            // Asegurar que sea un código HTTP válido (100-599)
            if (!is_numeric($code) || $code < 100 || $code > 599) {
                $code = 500;
            }
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $code);
        }
    }
}
