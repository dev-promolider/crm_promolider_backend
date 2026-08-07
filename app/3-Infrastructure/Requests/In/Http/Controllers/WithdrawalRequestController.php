<?php

namespace Promolider\Infrastructure\Requests\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Promolider\Application\Requests\UseCases\Withdrawal\ListWithdrawalRequestsUseCase;
use Promolider\Application\Requests\UseCases\Withdrawal\ListApprovedWithdrawalRequestsUseCase;
use Promolider\Application\Requests\UseCases\Withdrawal\ApproveWithdrawalRequestUseCase;
use Promolider\Application\Requests\UseCases\Withdrawal\RejectWithdrawalRequestUseCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Helper;

class WithdrawalRequestController extends Controller
{
    private $listUseCase;
    private $listApprovedUseCase;
    private $approveUseCase;
    private $rejectUseCase;

    public function __construct(
        ListWithdrawalRequestsUseCase $listUseCase,
        ListApprovedWithdrawalRequestsUseCase $listApprovedUseCase,
        ApproveWithdrawalRequestUseCase $approveUseCase,
        RejectWithdrawalRequestUseCase $rejectUseCase
    ) {
        $this->listUseCase = $listUseCase;
        $this->listApprovedUseCase = $listApprovedUseCase;
        $this->approveUseCase = $approveUseCase;
        $this->rejectUseCase = $rejectUseCase;
        // Assuming there is a policy for admin
        // $this->middleware('can:admin-action');
    }

    public function index()
    {
        Log::info('WithdrawalRequestController: Iniciando obtención de lista de solicitudes', [
            'authenticated_user' => auth()->id()
        ]);

        try {
            $requests = $this->listUseCase->execute();
            
            // Map entities to array
            $data = $requests->map(function ($req) {
                return $req->toArray();
            });

            return response()->json($data, 200);

        } catch (\Exception $e) {
            Log::error('WithdrawalRequestController: Error al obtener lista', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'No se pudieron cargar las solicitudes pendientes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function approvedHistory(Request $request)
    {
        Log::info('WithdrawalRequestController: Iniciando obtención de historial aprobado', [
            'authenticated_user' => auth()->id()
        ]);

        try {
            $perPage = $request->input('per_page', 15);
            $paginated = $this->listApprovedUseCase->execute($perPage);

            return response()->json($paginated, 200);
        } catch (\Exception $e) {
            Log::error('WithdrawalRequestController: Error al obtener historial aprobado', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'No se pudo cargar el historial',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request)
    {
        $requestId = $request->id;

        Log::info('WithdrawalRequestController: Iniciando aprobación de solicitud', [
            'request_id' => $requestId,
            'authenticated_user' => auth()->id(),
            'has_support_image' => $request->hasFile('support_image'),
            'message' => $request->message
        ]);

        try {
            $supportImageUrl = null;
            
            if ($request->hasFile('support_image')) {
                $image = $request->file('support_image');
                $formattedFilename = Helper::formatFilename($image->getClientOriginalName());
                $path = 'support_images/' . $formattedFilename;

                $options = [
                    'visibility' => 'public',
                    'ContentDisposition' => 'attachment; filename="' . $formattedFilename . '"',
                ];

                Storage::disk('s3')->put($path, file_get_contents($image), $options);
                $supportImageUrl = Storage::disk('s3')->url($path);
                
                Log::info('WithdrawalRequestController: Nueva imagen guardada exitosamente', [
                    'new_image_url' => $supportImageUrl,
                    'path' => $path
                ]);
            }

            $this->approveUseCase->execute($requestId, $request->message ?? '', $supportImageUrl);

            return response()->json([
                'message' => 'Solicitud aprobada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('WithdrawalRequestController: Error al aprobar solicitud', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'authenticated_user' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor: ' . $e->getMessage()], 500);
        }
    }

    public function reject(Request $request)
    {
        $requestId = $request->id;

        Log::info('WithdrawalRequestController: Iniciando rechazo de solicitud', [
            'request_id' => $requestId,
            'authenticated_user' => auth()->id()
        ]);

        try {
            $this->rejectUseCase->execute($requestId);

            return response()->json([
                'message' => 'Solicitud rechazada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('WithdrawalRequestController: Error al rechazar solicitud', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'authenticated_user' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}
