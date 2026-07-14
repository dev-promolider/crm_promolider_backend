<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Reports\GetContentReportUseCase;
use Promolider\Application\Marketing\UseCases\Reports\GetPrivateContentReportUseCase;
use Promolider\Application\Marketing\UseCases\Reports\GetStudentsReportUseCase;
use Promolider\Application\Marketing\UseCases\Reports\GetGeneralReportsUseCase;

class ReportsController extends Controller
{
    public function __construct(
        private readonly GetContentReportUseCase $getContentReportUseCase,
        private readonly GetPrivateContentReportUseCase $getPrivateContentReportUseCase,
        private readonly GetStudentsReportUseCase $getStudentsReportUseCase,
        private readonly GetGeneralReportsUseCase $getGeneralReportsUseCase,
    ) {}

    public function getMasterclassReport(Request $request, string $view): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->get('user_id');
            $data = $this->getContentReportUseCase->execute('masterclass', $view, $userId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting masterclass report: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener reporte'], 500);
        }
    }

    public function getMiniCourseReport(Request $request, string $view): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->get('user_id');
            $data = $this->getContentReportUseCase->execute('minicourse', $view, $userId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting mini course report: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener reporte'], 500);
        }
    }

    public function getEbookReport(Request $request, string $view): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->get('user_id');
            $data = $this->getContentReportUseCase->execute('ebook', $view, $userId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting ebook report: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener reporte'], 500);
        }
    }

    public function getPrivateContent(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getPrivateContentReportUseCase->getAll();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting private content report: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener contenido privado'], 500);
        }
    }

    public function getPrivateContentStudents(string $contentType, int $contentId): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getStudentsReportUseCase->getPrivateContentStudents($contentType, $contentId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting private content students: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener estudiantes'], 500);
        }
    }

    public function getContentByStatus(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getPrivateContentReportUseCase->getContentByStatus();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting content by status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener contenido por estado'], 500);
        }
    }

    public function getContentByProducer(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getPrivateContentReportUseCase->getContentByProducer();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting content by producer: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener contenido por productor'], 500);
        }
    }

    public function getGeneralReports(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getGeneralReportsUseCase->execute();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting general reports: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener reportes generales'], 500);
        }
    }

    public function getDistributors(string $type, int $contentId): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getStudentsReportUseCase->getDistributors($type, $contentId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting distributors: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener distribuidores'], 500);
        }
    }

    public function getStudents(string $type, int $contentId): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getStudentsReportUseCase->getStudents($type, $contentId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting students: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener estudiantes'], 500);
        }
    }

    public function getPendingParticipants(string $type, int $contentId): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getStudentsReportUseCase->getPendingParticipants($type, $contentId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting pending participants: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener participantes pendientes'], 500);
        }
    }

    public function getStudentsList(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $data = $this->getStudentsReportUseCase->getAllStudentsList($userId);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Lista de estudiantes obtenida',
                'total' => count($data)
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting students list: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener estudiantes'], 500);
        }
    }

    public function getAllPendingParticipantsByUser(Request $request, ?int $isParticipant = null): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $data = $this->getStudentsReportUseCase->getAllParticipantsByUser($userId, $isParticipant);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting all participants by user: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener participantes'], 500);
        }
    }

    public function getLastSells(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $limit = $request->input('n_sells', 5);
            $data = $this->getStudentsReportUseCase->getLastSells($userId, (int) $limit);
            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data recuperada con exito'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting last sells: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Ocurrio un error ' . $e->getMessage()], 500);
        }
    }
}
