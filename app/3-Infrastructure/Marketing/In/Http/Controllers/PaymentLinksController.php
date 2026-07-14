<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\PaymentLinks\ListPaymentLinksUseCase;
use Promolider\Application\Marketing\UseCases\PaymentLinks\GetPaymentLinkUseCase;
use Promolider\Application\Marketing\UseCases\PaymentLinks\GetPublicPaymentLinkUseCase;
use Promolider\Application\Marketing\UseCases\PaymentLinks\CreatePaymentLinkUseCase;
use Promolider\Application\Marketing\UseCases\PaymentLinks\UpdatePaymentLinkUseCase;
use Promolider\Application\Marketing\UseCases\PaymentLinks\TogglePaymentLinkUseCase;
use Promolider\Application\Marketing\UseCases\PaymentLinks\DeletePaymentLinkUseCase;

class PaymentLinksController extends Controller
{
    public function __construct(
        private ListPaymentLinksUseCase $listUseCase,
        private GetPaymentLinkUseCase $getUseCase,
        private GetPublicPaymentLinkUseCase $getPublicUseCase,
        private CreatePaymentLinkUseCase $createUseCase,
        private UpdatePaymentLinkUseCase $updateUseCase,
        private TogglePaymentLinkUseCase $toggleUseCase,
        private DeletePaymentLinkUseCase $deleteUseCase,
    ) {}

    // ─── Admin CRUD ────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'active', 'product_type']);
            $links = $this->listUseCase->execute($filters);
            return response()->json(['success' => true, 'data' => $links]);
        } catch (\Exception $e) {
            Log::error('Error listing payment links: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener enlaces de pago'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $link = $this->getUseCase->execute($id);
            if (!$link) {
                return response()->json(['success' => false, 'message' => 'Enlace no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $link]);
        } catch (\Exception $e) {
            Log::error('Error showing payment link: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener enlace'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:160|unique:payment_links,slug',
                'product_type' => 'nullable|string|max:50',
                'product_id' => 'nullable|integer',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:500',
                'active' => 'nullable|boolean',
            ]);

            $data['active'] = $data['active'] ?? true;

            $link = $this->createUseCase->execute($data);
            return response()->json(['success' => true, 'data' => $link], 201);
        } catch (\Exception $e) {
            Log::error('Error creating payment link: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear enlace: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'slug' => 'nullable|string|max:160|unique:payment_links,slug,' . $id,
                'product_type' => 'nullable|string|max:50',
                'product_id' => 'nullable|integer',
                'amount' => 'sometimes|numeric|min:0',
                'description' => 'nullable|string|max:500',
                'active' => 'nullable|boolean',
            ]);

            $link = $this->updateUseCase->execute($id, $data);
            if (!$link) {
                return response()->json(['success' => false, 'message' => 'Enlace no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $link]);
        } catch (\Exception $e) {
            Log::error('Error updating payment link: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar enlace'], 500);
        }
    }

    public function toggle(int $id): JsonResponse
    {
        try {
            $link = $this->toggleUseCase->execute($id);
            if (!$link) {
                return response()->json(['success' => false, 'message' => 'Enlace no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $link]);
        } catch (\Exception $e) {
            Log::error('Error toggling payment link: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->deleteUseCase->execute($id);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Enlace no encontrado'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Enlace eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting payment link: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar enlace'], 500);
        }
    }

    // ─── Public Endpoint ───────────────────────────────────────────────────

    public function publicShow(string $slug): JsonResponse
    {
        try {
            $link = $this->getPublicUseCase->execute($slug);
            if (!$link) {
                return response()->json(['success' => false, 'message' => 'Enlace no encontrado o inactivo'], 404);
            }
            return response()->json(['success' => true, 'data' => $link]);
        } catch (\Exception $e) {
            Log::error('Error showing public payment link: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener enlace de pago'], 500);
        }
    }
}
