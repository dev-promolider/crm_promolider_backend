<?php
// app/Http/Controllers/TestEmailController.php

namespace App\Http\Controllers;

use App\Services\PHPMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestEmailController extends Controller
{
    private $phpMailerService;

    public function __construct(PHPMailerService $phpMailerService)
    {
        $this->phpMailerService = $phpMailerService;
    }

    /**
     * Muestra el formulario de prueba de email
     */
    public function showForm()
    {
        return view('emails.test-form');
    }

    /**
     * Prueba rápida desde URL
     */
    public function testPHPMailer(Request $request)
    {
        $email = $request->get('email', 'josuechikorita@gmail.com');
        
        try {
            $result = $this->phpMailerService->sendTestEmail($email);
            
            return response()->json([
                'success' => true,
                'message' => "Correo de prueba enviado exitosamente a {$email}",
                'result' => $result,
                'timestamp' => now()->format('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error en prueba de PHPMailer', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error enviando correo de prueba',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envía email de prueba desde formulario
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string'
        ]);

        try {
            // Si se proporcionan subject y message personalizados
            if ($request->filled(['subject', 'message'])) {
                $result = $this->phpMailerService->sendEmail(
                    $request->email,
                    $request->subject,
                    nl2br(e($request->message)) // Convierte saltos de línea y escapa HTML
                );
            } else {
                // Usar plantilla por defecto
                $result = $this->phpMailerService->sendTestEmail($request->email);
            }
            
            return back()->with('success', "Correo enviado exitosamente a {$request->email}");
        } catch (\Exception $e) {
            Log::error('Error enviando correo desde formulario', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Error enviando correo: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario avanzado de pruebas
     */
    public function showTestForm()
    {
        return view('emails.advanced-test-form');
    }
}