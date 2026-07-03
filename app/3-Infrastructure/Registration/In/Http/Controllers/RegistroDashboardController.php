<?php
namespace Promolider\Infrastructure\Registration\In\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

use Promolider\Application\Registration\UseCases\GenerateSponsorLinkUseCase;
use Promolider\Application\Registration\UseCases\GetActiveSponsorLinkUseCase;
use Promolider\Application\Registration\UseCases\SuspendSponsorLinkUseCase;
use Promolider\Application\Registration\UseCases\GetRegisteredDirectsUseCase;

class RegistroDashboardController extends Controller
{
    private GenerateSponsorLinkUseCase $generateSponsorLinkUseCase;
    private GetActiveSponsorLinkUseCase $getActiveSponsorLinkUseCase;
    private SuspendSponsorLinkUseCase $suspendSponsorLinkUseCase;
    private GetRegisteredDirectsUseCase $getRegisteredDirectsUseCase;

    public function __construct(
        GenerateSponsorLinkUseCase $generateSponsorLinkUseCase,
        GetActiveSponsorLinkUseCase $getActiveSponsorLinkUseCase,
        SuspendSponsorLinkUseCase $suspendSponsorLinkUseCase,
        GetRegisteredDirectsUseCase $getRegisteredDirectsUseCase
    ) {
        $this->generateSponsorLinkUseCase = $generateSponsorLinkUseCase;
        $this->getActiveSponsorLinkUseCase = $getActiveSponsorLinkUseCase;
        $this->suspendSponsorLinkUseCase = $suspendSponsorLinkUseCase;
        $this->getRegisteredDirectsUseCase = $getRegisteredDirectsUseCase;
    }

    public function getActiveLink(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->getActiveSponsorLinkUseCase->execute($userId);
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            Log::error('Error en getActiveLink: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function generateLink(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->generateSponsorLinkUseCase->execute($userId);
            
            $status = $result['success'] ? 200 : 400;
            return response()->json($result, $status);
        } catch (\Exception $e) {
            Log::error('Error en generateLink: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function suspendLink(Request $request, int $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->suspendSponsorLinkUseCase->execute($id, $userId);
            
            $status = $result['success'] ? 200 : 403;
            return response()->json($result, $status);
        } catch (\Exception $e) {
            Log::error('Error en suspendLink: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function getDirects(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $result = $this->getRegisteredDirectsUseCase->execute($userId);
            
            return response()->json($result, 200);
        } catch (\Exception $e) {
            Log::error('Error en getDirects: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
