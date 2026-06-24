<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MarketingService;
use App\Services\ReportService;
use App\Services\CalendarService;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    protected $marketingService;
    protected $reportService;
    protected $calendarService;
    protected $categoryService;

    public function __construct(
        MarketingService $marketingService,
        ReportService $reportService,
        CalendarService $calendarService,
        CategoryService $categoryService
    ) {
        $this->marketingService = $marketingService;
        $this->reportService = $reportService;
        $this->calendarService = $calendarService;
        $this->categoryService = $categoryService;

        // Middleware consolidado
        $this->middleware('can:marketing.tools')->only(['tools', 'createMiniCourse', 'createEbook']);
        $this->middleware('can:marketing.calendar')->only(['calendar']);
        $this->middleware('can:masterclass.marketplace')->only(['marketplace', 'marketplaceDetails']);
        $this->middleware('can:marketing.report')->only(['report']);
        $this->middleware('can:marketing.pages')->only(['pages']);
    }

    // ============= VISTAS =============
    
    public function index()
    {
        return $this->renderView('content.marketing.index', ['permission' => 'marketing.tools']);
    }

    public function pages()
    {
        return $this->renderView('content.marketing.pages', ['permission' => 'marketing.pages', 'role' => true]);
    }

    public function payments_link()
    {
        return view('content.marketing.payments');
    }

    public function report()
    {
        return $this->renderView('content.marketing.report', ['permission' => 'marketing.report', 'role' => true]);
    }

    public function myReports()
    {
        return $this->renderView('content.marketing.my-report', ['permission' => 'marketing.report', 'role' => true]);
    }

    public function generalReports()
    {
        return $this->renderView('content.marketing.general-reports', ['permission' => 'marketing.report', 'role' => true]);
    }

    public function marketplaceIndex()
    {
        return $this->renderView('content.marketing.marketplaceIndex', ['permission' => 'masterclass.create', 'role' => true]);
    }

    public function tools()
    {
        return $this->renderView('content.marketing.tools', ['permission' => 'marketing.create']);
    }

    public function calendar()
    {
        return $this->renderView('content.marketing.calendar', ['permission' => 'marketing.calendar', 'role' => true]);
    }

    public function marketplace()
    {
        return $this->renderView('content.marketing.marketplace.index', [
            'permission' => 'masterclass.marketplace',
            'user_role' => true
        ]);
    }

    // ============= HERRAMIENTAS =============
    
    public function list()
    {
        return $this->marketingService->getToolsByUser(Auth::id());
    }

    public function getAllTools()
    {
        return $this->marketingService->getToolsByUser(Auth::id());
    }

    public function getCampaigns()
    {
        return $this->marketingService->getCampaigns();
    }

    public function getCampaignsByType($type)
    {
        return $this->marketingService->getCampaignsByType($type);
    }

    public function getCategories()
    {
        return response()->json($this->categoryService->getAllForApi());
    }

    // ============= MARKETPLACE - MASTERCLASS =============
    
    public function masterclassList()
    {
        return $this->marketingService->getMasterclassList();
    }

    public function masterclassesPaginated(Request $request)
    {
        return $this->marketingService->getMasterclassesPaginated(
            $request->get('pageNumber', 1),
            $request->get('pageSize', 10)
        );
    }

    public function masterclassDetails($id)
    {
        $masterclass = $this->marketingService->getMasterclassDetails($id);
        return $this->renderView('content.marketing.marketplace.details', [
            'permission' => 'masterclass.marketplace',
            'masterclass' => $masterclass
        ]);
    }

    // ============= MARKETPLACE - EBOOKS =============
    
    public function ebooksList()
    {
        return $this->marketingService->getEbooksList();
    }

    public function ebooksPaginated(Request $request)
    {
        return $this->marketingService->getEbooksPaginated(
            $request->get('pageNumber', 1),
            $request->get('pageSize', 10)
        );
    }

    public function ebookDetails($id)
    {
        $ebook = $this->marketingService->getEbookDetails($id);
        return $this->renderView('content.marketing.e-book.details', [
            'permission' => 'masterclass.marketplace',
            'ebook' => $ebook
        ]);
    }

    // ============= MARKETPLACE - MINI CURSOS =============
    
    public function miniCoursesList()
    {
        return $this->marketingService->getMiniCoursesList();
    }

    public function miniCoursesPaginated(Request $request)
    {
        return $this->marketingService->getMiniCoursesPaginated(
            $request->get('pageNumber', 1),
            $request->get('pageSize', 10)
        );
    }

    public function miniCourseDetails($id)
    {
        $miniCourse = $this->marketingService->getMiniCourseDetails($id);
        return $this->renderView('content.marketing.mini-course.details', [
            'permission' => 'masterclass.marketplace',
            'miniCourse' => $miniCourse
        ]);
    }

    // ============= CALENDARIO =============
    
    public function calendarAdmin()
    {
        return $this->calendarService->getAdminCalendar();
    }

    public function calendarProducer($id)
    {
        return $this->calendarService->getProducerCalendar($id);
    }

    public function calendarDistributor($id)
    {
        return $this->calendarService->getDistributorCalendar($id);
    }

    public function getActivities($id)
    {
        return $this->calendarService->getActivities($id);
    }

    public function createMeeting(Request $request)
    {
        return $this->calendarService->createMeeting($request);
    }

    public function getNotes(Request $request)
    {
        return $this->calendarService->getNotes(
            Auth::id(),
            $request->get('start_date'),
            $request->get('end_date')
        );
    }

    public function syncNotes(Request $request)
    {
        return $this->calendarService->syncNotes(Auth::id(), $request->input('notes'));
    }

    public function createNote(Request $request)
    {
        return $this->calendarService->createNote(Auth::id(), $request->all());
    }

    public function updateNote(Request $request, $id)
    {
        return $this->calendarService->updateNote(Auth::id(), $id, $request->all());
    }

    public function deleteNote($id)
    {
        return $this->calendarService->deleteNote(Auth::id(), $id);
    }

    // ============= REPORTES =============
    
    // ============= DISTRIBUIDORES =============

    public function listMasterclassDistributors($id)
    {
        return $this->marketingService->getMasterclassDistributors($id);
    }

    public function listMiniCourseDistributors($id)
    {
        return $this->marketingService->getMiniCourseDistributors($id);
    }

    public function listEbookDistributors($id)
    {
        return $this->marketingService->getEbookDistributors($id);
    }

    // Reportes de Masterclass
    public function reportMasterclassAdmin_M()
    {
        return $this->reportService->getMasterclassReportByAdmin('masterclass');
    }



    public function reportMasterclassAdmin_D()
    {
        return $this->reportService->getMasterclassReportByAdmin('distributor');
    }

    public function reportMasterclassProducer_M($producerId)
    {
        return $this->reportService->getProducerReport('masterclass', $producerId);
    }

    public function reportMasterclassProducer_D($id)
    {
        return $this->reportService->getProducerReport('masterclass', $id, 'distributor');
    }

    public function reportMasterclassDistributor($id)
    {
        return $this->reportService->getDistributorReport('masterclass', $id);
    }

    // Reportes de Mini Cursos
    public function reportMiniCourseAdmin_M()
    {
        return $this->reportService->getMiniCourseReportByAdmin('minicourse');
    }

    public function reportMiniCourseAdmin_D()
    {
        return $this->reportService->getMiniCourseReportByAdmin('distributor');
    }

    public function reportMiniCourseProducer_M($id)
    {
        return $this->reportService->getProducerReport('minicourse', $id);
    }

    public function reportMiniCourseProducer_D($id)
    {
        return $this->reportService->getProducerReport('minicourse', $id, 'distributor');
    }

    public function reportMiniCourseDistributor($id)
    {
        return $this->reportService->getDistributorReport('minicourse', $id);
    }

    // Reportes de Ebooks
    public function reportEbookAdmin_M()
    {
        return $this->reportService->getEbookReportByAdmin('ebook');
    }

    public function reportEbookAdmin_D()
    {
        return $this->reportService->getEbookReportByAdmin('distributor');
    }

    public function reportEbookProducer_M($id)
    {
        return $this->reportService->getProducerReport('ebook', $id);
    }

    public function reportEbookProducer_D($id)
    {
        return $this->reportService->getProducerReport('ebook', $id, 'distributor');
    }

    public function reportEbookDistributor($id)
    {
        return $this->reportService->getDistributorReport('ebook', $id);
    }

    // Reportes de Contenido Privado
    public function reportPrivateMasterclasses()
    {
        return $this->reportService->getPrivateContentReport('masterclass');
    }

    public function reportPrivateMasterclassStudents($masterclassId)
    {
        return $this->reportService->getPrivateContentStudents('masterclass', $masterclassId);
    }

    public function reportPrivateMiniCourses()
    {
        return $this->reportService->getPrivateContentReport('minicourse');
    }

    public function reportPrivateMiniCourseStudents($minicourseId)
    {
        return $this->reportService->getPrivateContentStudents('minicourse', $minicourseId);
    }

    public function reportPrivateEbooks()
    {
        return $this->reportService->getPrivateContentReport('ebook');
    }

    public function reportPrivateEbookStudents($ebookId)
    {
        return $this->reportService->getPrivateContentStudents('ebook', $ebookId);
    }

    public function reportAllPrivateContent()
    {
        return $this->reportService->getAllPrivateContent();
    }

    public function reportPrivateContentByProducer($producerId)
    {
        return $this->reportService->getPrivateContentByProducer($producerId);
    }

    public function reportContentByStatus()
    {
        return $this->reportService->getContentByStatus();
    }

    public function reportContentByProducer()
    {
        return $this->reportService->getPrivateContentByProducer(Auth::id());
    }

    // ============= ESTUDIANTES Y PARTICIPANTES =============
    
    public function getPendingParticipants($id, $type = 'masterclass')
    {
        return $this->marketingService->getPendingParticipants($id, $type);
    }

    public function listStudents($id, $type = 'masterclass')
    {
        return $this->marketingService->listStudents($id, $type, Auth::id());
    }

    public function listMasterclassStudents($id)
    {
        return $this->listStudents($id, 'masterclass');
    }

    public function listMinicourseStudents($id)
    {
        return $this->listStudents($id, 'minicourse');
    }

    public function listEbookStudents($id)
    {
        return $this->listStudents($id, 'ebook');
    }

    public function getStudentsList()
    {
        return $this->marketingService->getStudentsList(Auth::id());
    }

    public function getAllPendingParticipantsByUser($isParticipant = null)
    {
        return $this->marketingService->getAllParticipantsByUser(Auth::id(), $isParticipant);
    }

    public function getAllPendingParticipants()
    {
        return $this->getAllPendingParticipantsByUser(0);
    }

    public function getAllConfirmedParticipants()
    {
        return $this->getAllPendingParticipantsByUser(1);
    }

    public function getAllParticipantsStatus2()
    {
        return $this->getAllPendingParticipantsByUser(2);
    }

    public function getAllParticipants()
    {
        return $this->getAllPendingParticipantsByUser();
    }

    // ============= VALIDACIONES =============
    
    public function validateDistributor(Request $request)
    {
        return $this->marketingService->validateDistributor(
            Auth::id(),
            $request->input('distributor_name')
        );
    }

    /**
     * Verifica si una herramienta pertenece al usuario autenticado
     * (como productor o distribuidor)
     */
    public function verifyToolOwnership($type, $id)
    {
        try {
            // Validar el tipo
            if (!in_array($type, ['masterclass', 'minicourse', 'ebook'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de contenido no válido'
                ], 400);
            }
        
            // Validar que el ID sea numérico
            if (!is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID inválido'
                ], 400);
            }
        
            $result = $this->marketingService->verifyToolOwnership(
                Auth::id(),
                $type,
                $id
            );
        
            return response()->json($result);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar propiedad: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============= MÉTODOS AUXILIARES =============
    
    /**
     * Renderiza una vista con datos del usuario autenticado
     */
    private function renderView(string $view, array $options = [])
    {
        $user = Auth::user();
        
        $data = ['user' => $user];
        
        if (isset($options['permission'])) {
            $permission = $user->hasPermissionTo($options['permission']);
            if (!$permission && request()->expectsJson()) {
                return response()->json([
                    'message' => 'No tienes permisos para acceder a esta sección'
                ], 403);
            }
            $data['permission'] = $permission;
        }
        
        if (isset($options['role']) && $options['role']) {
            $data['role'] = $user->getRoleNames()->first();
        }
        
        if (isset($options['user_role']) && $options['user_role']) {
            $data['user_role'] = $user->getRoleNames()->first();
        }
        
        // Agregar datos adicionales
        foreach ($options as $key => $value) {
            if (!in_array($key, ['permission', 'role', 'user_role'])) {
                $data[$key] = $value;
            }
        }
        
        return view($view, $data);
    }
}