<?php

namespace Promolider\Infrastructure\RoleRequests\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Promolider\Application\RoleRequests\UseCases\ListRoleRequestsUseCase;
use Promolider\Application\RoleRequests\UseCases\ApproveRoleRequestUseCase;
use Promolider\Application\RoleRequests\UseCases\RejectRoleRequestUseCase;

class RoleRequestController extends Controller
{
    private $listUseCase;
    private $approveUseCase;
    private $rejectUseCase;

    public function __construct(
        ListRoleRequestsUseCase $listUseCase,
        ApproveRoleRequestUseCase $approveUseCase,
        RejectRoleRequestUseCase $rejectUseCase
    ) {
        $this->listUseCase = $listUseCase;
        $this->approveUseCase = $approveUseCase;
        $this->rejectUseCase = $rejectUseCase;
    }

    public function listCourseRequests()
    {
        $users = $this->listUseCase->executeCourseRequests();
        return response()->json(['data' => $users]);
    }

    public function listToolRequests()
    {
        $users = $this->listUseCase->executeToolRequests();
        return response()->json(['data' => $users]);
    }

    public function approveCourseRequest(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $userId = $request->id;
        \Log::info('[ApproveRole] Recibida petición para user_id=' . $userId);
        try {
            $this->approveUseCase->executeCourseRequest($userId);
            \Log::info('[ApproveRole] Aprobado correctamente user_id=' . $userId);
            return response()->json(['message' => 'Permisos otorgados con éxito'], 200);
        } catch (\Exception $e) {
            \Log::error('[ApproveRole] Error para user_id=' . $userId . ': ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function approveToolRequest(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        try {
            $this->approveUseCase->executeToolRequest($request->id);
            return response()->json(['message' => 'Permisos otorgados con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectCourseRequest(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'justification' => 'required|string']);
        try {
            $this->rejectUseCase->executeCourseRequest($request->id, $request->justification);
            return response()->json(['message' => 'Solicitud rechazada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectToolRequest(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'justification' => 'required|string']);
        try {
            $this->rejectUseCase->executeToolRequest($request->id, $request->justification);
            return response()->json(['message' => 'Solicitud rechazada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
