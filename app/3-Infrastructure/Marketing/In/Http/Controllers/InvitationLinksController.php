<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\InvitationLinks\CreateInvitationLinkUseCase;
use Promolider\Application\Marketing\UseCases\InvitationLinks\CheckInvitationUseCase;

class InvitationLinksController extends Controller
{
    public function __construct(
        private CreateInvitationLinkUseCase $createUseCase,
        private CheckInvitationUseCase $checkUseCase,
    ) {}

    public function create(Request $request, string $productType, int $productId): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $allowedTypes = ['masterclass', 'ebook', 'mini-course', 'minicourse'];

            if (!in_array($productType, $allowedTypes)) {
                return response()->json(['success' => false, 'message' => 'Tipo de producto inválido'], 400);
            }

            $result = $this->createUseCase->execute($productType, $productId, $userId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error creating invitation link: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear enlace de invitación'], 500);
        }
    }

    public function check(Request $request, string $productType, int $productId): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $allowedTypes = ['masterclass', 'ebook', 'mini-course', 'minicourse'];

            if (!in_array($productType, $allowedTypes)) {
                return response()->json(['success' => false, 'message' => 'Tipo de producto inválido'], 400);
            }

            $result = $this->checkUseCase->execute($productType, $productId, $userId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error checking invitation: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al verificar invitación'], 500);
        }
    }
}
