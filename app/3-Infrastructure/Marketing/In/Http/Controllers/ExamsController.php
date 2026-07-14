<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Models\Clas;
use App\Models\Course;
use App\Models\CourseConfiguration;
use App\Models\Exam as ExamModel;
use App\Models\ExamQuestion;
use App\Models\Module;
use App\Models\PurchasedCourse;
use App\Models\User;
use App\Models\UserCertificate;
use App\Models\UserExamHeader;
use App\Models\UserQuestionAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Exams\GetActiveExamUseCase;
use Promolider\Application\Marketing\UseCases\Exams\SubmitExamAnswersUseCase;
use Promolider\Application\Marketing\UseCases\Exams\GetExamResultsUseCase;

class ExamsController extends Controller
{
    public function __construct(
        private GetActiveExamUseCase $getActiveExamUseCase,
        private SubmitExamAnswersUseCase $submitExamAnswersUseCase,
        private GetExamResultsUseCase $getExamResultsUseCase,
    ) {}

    /**
     * GET /marketing/courses/{courseId}/exam/active
     * Obtiene el examen activo para el curso/módulo/clase.
     */
    public function active(Request $request, int $courseId)
    {
        try {
            $request->validate([
                'exam_type' => 'required|string|in:course,module,class',
                'id_type' => 'required|integer|min:1',
            ]);

            $userId = $request->user()->id;

            $result = $this->getActiveExamUseCase->execute(
                $request->input('exam_type'),
                (int) $request->input('id_type'),
                $userId
            );

            return response()->json($result);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 400;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /marketing/courses/{courseId}/exam/submit
     * Envía las respuestas del examen para calificación.
     */
    public function submit(Request $request, int $courseId)
    {
        try {
            $data = $request->validate([
                'exam_id' => 'required|integer|min:1',
                'answers' => 'required|array',
                'answers.*.option' => 'nullable',
            ]);

            $userId = $request->user()->id;

            $result = $this->submitExamAnswersUseCase->execute(
                (int) $data['exam_id'],
                $userId,
                $data['answers'],
                $courseId
            );

            return response()->json($result);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 400;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * GET /marketing/courses/{courseId}/exam/results
     * Obtiene los resultados del examen para el usuario.
     */
    public function results(Request $request, int $courseId)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer|min:1',
            ]);

            $userId = $request->user()->id;

            $result = $this->getExamResultsUseCase->execute(
                (int) $request->input('exam_id'),
                $userId
            );

            return response()->json($result);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 404;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /marketing/courses/{courseId}/exam/calification
     * Obtiene la calificación de un examen específico (por lección) para un usuario.
     */
    public function getCalification(Request $request, int $courseId): JsonResponse
    {
        try {
            $request->validate([
                'lesson_id' => 'required|integer|min:1',
            ]);

            $lessonId = (int) $request->input('lesson_id');
            $userId = $request->user()->id;

            $exam = ExamModel::where('lesson_id', $lessonId)->first();

            if (!$exam) {
                return response()->json(['success' => false, 'message' => 'Examen no realizado']);
            }

            $detail = UserExamHeader::where('exam_id', $exam->id)
                ->where('user_id', $userId)
                ->first();

            $productor = User::find($exam->productor_id);
            $teacherName = $productor ? $productor->name . ' ' . $productor->last_name : '';

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $exam->title,
                    'teacher' => $teacherName,
                    'max_score' => $exam->max_score,
                    'calification' => $detail?->rate,
                    'status' => $detail?->condition,
                ],
                'message' => $detail ? 'Examen ya realizado' : 'Examen no realizado'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error getting exam calification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener calificación'], 500);
        }
    }

    /**
     * POST /marketing/courses/{courseId}/exam/module/active
     * Obtiene exámenes activos a nivel curso + nivel módulo.
     */
    public function getActiveModuleExams(Request $request, int $courseId): JsonResponse
    {
        try {
            $request->validate(['id_course' => 'required|integer']);
            $courseId = (int) $request->input('id_course');
            $userId = $request->user()->id;

            // Examen a nivel curso
            $examCourse = ExamModel::select('id', 'title')
                ->where(['course_id' => $courseId, 'module_id' => null, 'lesson_id' => null, 'status' => 1])
                ->first();
            $examCourse = $this->validateExamForUser($userId, $examCourse);

            // Exámenes a nivel módulo
            $modules = Module::select('id')->where('id_courses', $courseId)->get();
            $moduleExams = [];
            foreach ($modules as $module) {
                $exam = ExamModel::select('id', 'title')
                    ->where(['module_id' => $module->id, 'lesson_id' => null, 'status' => 1])
                    ->first();
                $validated = $this->validateExamForUser($userId, $exam);
                if ($validated) {
                    $moduleExams[] = $validated;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'course_exam' => $examCourse ?? 'No existe el examen',
                    'module_exams' => count($moduleExams) > 0 ? $moduleExams : 'No existe el examen',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting active module exams: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener exámenes activos'], 500);
        }
    }

    /**
     * Valida si el usuario puede acceder al examen (no aprobado y < 3 intentos).
     */
    private function validateExamForUser(int $userId, $exam): ?object
    {
        if (!$exam) {
            return null;
        }

        $userHeaders = UserExamHeader::where(['exam_id' => $exam->id])
            ->where('user_id', $userId)
            ->get();

        if ($userHeaders->isEmpty()) {
            return $exam;
        }

        $isApproved = false;
        $attempts = 0;

        foreach ($userHeaders as $header) {
            if ($header->condition === 'Approved') {
                $isApproved = true;
                break;
            }
            $attempts++;
        }

        if (!$isApproved && $attempts < 3) {
            return $exam;
        }

        return null;
    }

    /**
     * GET /marketing/courses/{courseId}/exam/list
     * Lista todos los exámenes del curso con progreso del usuario.
     */
    public function examList(Request $request, int $courseId): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            // Clases con exámenes
            $classes = Module::join('class', 'modules.id', '=', 'class.id_modules')
                ->join('courses', 'modules.id_courses', '=', 'courses.id')
                ->where('courses.id', $courseId)
                ->where('courses.status', '!=', 0)
                ->select('class.id as class_id', 'class.name', 'class.slug')
                ->get();

            $counterClass = 0;
            $countApproved = 0;

            foreach ($classes as $class) {
                $exam = ExamModel::where(['lesson_id' => $class->class_id, 'status' => 1])->first();
                if ($exam) {
                    $class->exist = true;
                    $class->exam_id = $exam->id;
                    $userAttempt = UserExamHeader::where(['exam_id' => $exam->id, 'user_id' => $userId])->latest()->first();
                    $class->approved = $userAttempt ? $userAttempt->condition : false;
                    if ($userAttempt && $userAttempt->condition === 'Approved') {
                        $countApproved++;
                    }
                    $counterClass++;
                } else {
                    $class->exist = false;
                    $class->approved = false;
                }
            }

            // Módulos con exámenes
            $modules = Course::join('modules', 'courses.id', '=', 'modules.id_courses')
                ->where('courses.id', $courseId)
                ->select('modules.id as module_id', 'modules.name', 'modules.name as slug')
                ->get();

            $counterModule = 0;
            foreach ($modules as $module) {
                $exam = ExamModel::where(['module_id' => $module->module_id, 'status' => 1])->first();
                if ($exam) {
                    $module->exist = true;
                    $module->exam_id = $exam->id;
                    $userAttempt = UserExamHeader::where(['exam_id' => $exam->id, 'user_id' => $userId])->first();
                    $module->approved = $userAttempt ? $userAttempt->condition : false;
                    if ($userAttempt && $userAttempt->condition === 'Approved') {
                        $countApproved++;
                    }
                    $counterModule++;
                } else {
                    $module->exist = false;
                    $module->approved = false;
                }
            }

            // Examen del curso
            $courseData = (object) ['course_id' => $courseId];
            $examCourse = ExamModel::where(['course_id' => $courseId, 'status' => 1])->first();
            if ($examCourse) {
                $courseData->exist = true;
                $courseData->exam_id = $examCourse->id;
                $userAttempt = UserExamHeader::where(['exam_id' => $examCourse->id, 'user_id' => $userId])->first();
                $courseData->approved = $userAttempt ? $userAttempt->condition : false;
                if ($userAttempt && $userAttempt->condition === 'Approved') {
                    $countApproved++;
                }
                $counterCourse = 1;
            } else {
                $courseData->exist = false;
                $courseData->approved = false;
                $counterCourse = 0;
            }

            $totalExams = $counterClass + $counterModule + $counterCourse;
            $examProgress = $totalExams > 0 ? round(($countApproved / $totalExams) * 100) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'exams_class' => $classes,
                    'exams_module' => $modules,
                    'exam_course' => $courseData,
                    'counter_class' => $counterClass,
                    'counter_module' => $counterModule,
                    'counter_course' => $counterCourse,
                    'exam_progress' => $examProgress,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error listing exams: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar exámenes'], 500);
        }
    }

    /**
     * GET /marketing/courses/{courseId}/exam/isconfig
     * Verifica si el curso tiene configuración de certificado.
     */
    public function checkCourseConfig(int $courseId): JsonResponse
    {
        try {
            $config = CourseConfiguration::where('course_id', $courseId)->exists();
            return response()->json([
                'success' => true,
                'data' => ['is_configured' => $config]
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking course config: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al verificar configuración'], 500);
        }
    }

    /**
     * GET /marketing/courses/{courseId}/certificate/check
     * Verifica si el usuario puede reclamar el certificado y lo genera si cumple.
     */
    public function checkCertificate(Request $request, int $courseId): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $courseConfig = CourseConfiguration::where('course_id', $courseId)->first();
            if (!$courseConfig) {
                return response()->json(['success' => true, 'data' => ['can_claim' => false, 'message' => 'Curso no configurado']]);
            }

            // Verificar lecciones vistas
            $lessonSeen = [];
            $purchased = PurchasedCourse::where('course_id', $courseId)
                ->where('user_id', $userId)
                ->first();
            if ($purchased && $purchased->classes_status) {
                $lessonSeen = json_decode($purchased->classes_status, true) ?? [];
            }

            $meetsLessons = true;
            if ($courseConfig->condition_to_certificate != 1) {
                $meetsLessons = $this->validateLessonsForCertificate($lessonSeen, $courseId, $courseConfig);
            }

            // Verificar exámenes
            $meetsExams = true;
            if ($courseConfig->condition_to_certificate != 0) {
                $meetsExams = $this->validateExamsForCertificate($courseId, $userId);
            }

            // Condición final
            $canClaim = match ((int) $courseConfig->condition_to_certificate) {
                0 => $meetsLessons,
                1 => $meetsExams,
                2 => $meetsLessons && $meetsExams,
                default => false,
            };

            if ($canClaim) {
                // Generar certificado si no existe
                $existingCert = UserCertificate::where('id_user', $userId)
                    ->where('id_course', $courseId)
                    ->exists();

                if (!$existingCert) {
                    UserCertificate::create([
                        'id_user' => $userId,
                        'id_course' => $courseId,
                        'certificate' => 'pending',
                    ]);
                }

                // Marcar curso como completado
                if ($purchased && !$purchased->completed_date) {
                    $purchased->update([
                        'completed_course' => 1,
                        'completed_date' => now(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'can_claim' => $canClaim,
                    'meets_lessons' => $meetsLessons,
                    'meets_exams' => $meetsExams,
                    'condition_type' => $courseConfig->condition_to_certificate,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking certificate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al verificar certificado'], 500);
        }
    }

    /**
     * Valida que el usuario haya visto todas las lecciones requeridas.
     */
    private function validateLessonsForCertificate(array $lessonSeen, int $courseId, $courseConfig): bool
    {
        $validatedBy = $courseConfig->validated_by ?? 'course';

        if ($validatedBy === 'course') {
            $classIds = Module::join('class', 'modules.id', '=', 'class.id_modules')
                ->where('modules.id_courses', $courseId)
                ->pluck('class.id')
                ->toArray();
        } else {
            return true; // Simplificación: modulo/lesson validation es más complejo
        }

        foreach ($classIds as $id) {
            if (!array_key_exists($id, $lessonSeen)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Valida que el usuario haya aprobado todos los exámenes del curso.
     */
    private function validateExamsForCertificate(int $courseId, int $userId): bool
    {
        $allPassed = true;

        // Exámenes de clase
        $classIds = Module::join('class', 'modules.id', '=', 'class.id_modules')
            ->where('modules.id_courses', $courseId)
            ->pluck('class.id');

        foreach ($classIds as $classId) {
            $exam = ExamModel::where(['lesson_id' => $classId, 'status' => 1])->first();
            if ($exam) {
                $passed = UserExamHeader::where([
                    'exam_id' => $exam->id,
                    'user_id' => $userId,
                    'condition' => 'Approved',
                ])->exists();
                if (!$passed) return false;
            }
        }

        // Exámenes de módulo
        $moduleIds = Module::where('id_courses', $courseId)->pluck('id');
        foreach ($moduleIds as $moduleId) {
            $exam = ExamModel::where(['module_id' => $moduleId, 'status' => 1])->first();
            if ($exam) {
                $passed = UserExamHeader::where([
                    'exam_id' => $exam->id,
                    'user_id' => $userId,
                    'condition' => 'Approved',
                ])->exists();
                if (!$passed) return false;
            }
        }

        // Examen del curso
        $courseExam = ExamModel::where(['course_id' => $courseId, 'status' => 1])->first();
        if ($courseExam) {
            $passed = UserExamHeader::where([
                'exam_id' => $courseExam->id,
                'user_id' => $userId,
                'condition' => 'Approved',
            ])->exists();
            if (!$passed) return false;
        }

        return $allPassed;
    }

    /**
     * GET /marketing/courses/congratulations
     * Obtiene el certificado no visto para la pantalla de felicitación.
     */
    public function getCongratulations(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $certificate = PurchasedCourse::join('users', 'purchased_courses.user_id', '=', 'users.id')
                ->join('user_certificates', 'users.id', '=', 'user_certificates.id_user')
                ->where('purchased_courses.certificate_seen', 0)
                ->where('purchased_courses.user_id', $userId)
                ->select('user_certificates.certificate')
                ->first();

            return response()->json([
                'success' => true,
                'data' => $certificate
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting congratulations: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener felicitación'], 500);
        }
    }

    /**
     * POST /marketing/courses/{courseId}/exam/indicators
     * Indicadores de exámenes: total de usuarios, aprobados, tasa de aprobación.
     */
    public function getIndicators(Request $request, int $courseId): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $totalUsers = UserExamHeader::whereBetween('created_at', [$startDate, $endDate])
                ->select('user_id')->distinct()->count();

            $totalApprovedUsers = UserExamHeader::whereBetween('created_at', [$startDate, $endDate])
                ->where('condition', 'Aproved')
                ->select('user_id')->distinct()->count();

            $approvalRate = $totalUsers > 0 ? round(($totalApprovedUsers / $totalUsers) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'totalUsers' => $totalUsers,
                    'totalApprovedUsers' => $totalApprovedUsers,
                    'approvalRate' => $approvalRate,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error getting exam indicators: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener indicadores'], 500);
        }
    }
}
