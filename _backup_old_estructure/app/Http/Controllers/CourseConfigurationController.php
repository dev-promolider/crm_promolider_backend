<?php

namespace App\Http\Controllers;

use App\Models\Clas;
use App\Models\CourseConfiguration;
use App\Models\Module;
use App\Models\PurchasedCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CertificatesController;
use App\Models\Course;
use App\Models\UserCertificate;
use App\Models\Exam as ModelsExam;
use App\Models\User;
use App\Models\UserExamHeader;
use App\Models\Lesson;
use Illuminate\Support\Facades\Log;

class CourseConfigurationController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $type = $this->determineType($request->type);
            $data = $this->buildArray($type, $request->course, $request->module, $request->lesson, $request->type_certificate, $request->certificate_price);
            $configuration = new CourseConfiguration();
            $configuration->course_id = $request->course;
            $configuration->validated_by = $type;
            $configuration->data = $data;
            $configuration->type_certificate = $request->type_certificate;
            $configuration->condition_to_certificate = $request->condition_to_certificate;
            $configuration->customized_certificate = $request->customized_certificate;
            $configuration->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function determineType($number)
    {
        switch ($number) {
            case '1':
                $str = 'course';
                break;
            case '2':
                $str = 'module';
                break;
            case '3':
                $str = 'lesson';
                break;
            default:
                $str = '';
                break;
        }
        return $str;
    }

    public function saveCompletedLessons(Request $request, $course_id)
    {
        $request->validate([
            'completed_lessons' => 'required|array',
            'completed_lessons.*' => 'integer|exists:class,id'
        ]);

        $userId = auth()->id();

        try {
            $purchasedCourse = PurchasedCourse::where('course_id', $course_id)
                ->where('user_id', $userId)
                ->firstOrFail();

            // Convertir el array a JSON para guardarlo
            $purchasedCourse->lessons = json_encode($request->completed_lessons);
            $purchasedCourse->save();

            \Log::info("✅ Lecciones completadas guardadas", [
                'course_id' => $course_id,
                'user_id' => $userId,
                'lessons' => $request->completed_lessons
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lecciones completadas guardadas correctamente',
                'lessons' => $request->completed_lessons
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Error guardando lecciones completadas", [
                'course_id' => $course_id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las lecciones completadas'
            ], 500);
        }
    }

    public function buildArray($type, $course_id, $module_id = null, $lesson_id = null, $type_certificate, $certificate_price)
    {
        if ($type_certificate == 1) {
            $certificate_price = 0;
        }

        switch ($type) {
            case 'course':
                $json = array(
                    'course' => $course_id,
                    'certificate_price' => $certificate_price

                );
                break;
            case 'module':
                $json = array(
                    'course' => $course_id,
                    'module' => $module_id,
                    'certificate_price' => $certificate_price
                );
                break;
            case 'lesson':
                $json = array(
                    'course' => $course_id,
                    'module' => $module_id,
                    'lesson' => $lesson_id,
                    'certificate_price' => $certificate_price
                );
                break;
            default:
                $json = '';
                break;
        }
        return $json;
    }

    public function updateProgress(Request $request, $course_id)
    {
        \Log::info("🚀 updateProgress llamado", [
            'course_id' => $course_id,
            'user_id'   => auth()->id(),
            'request'   => $request->all(), // loguea todo lo que llega
        ]);
    
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100',
        ]);
    
        $userId = auth()->id();
    
        $purchasedCourse = PurchasedCourse::where('course_id', $course_id)
            ->where('user_id', $userId)
            ->firstOrFail();
        
        // Guardar el estado previo para detectar cuando se completa por primera vez
        $wasCompleted = $purchasedCourse->completed_course == 1;
    
        $purchasedCourse->progress = $request->progress;
    
        // Si llega al 100%, marcar como completado
        if ($request->progress >= 100) {
            $purchasedCourse->completed_course = 1;
            $purchasedCourse->completed_date = now();
        }
    
        $purchasedCourse->save();
    
        \Log::info("✅ Progreso actualizado", [
            'course_id' => $course_id,
            'user_id'   => $userId,
            'progress'  => $purchasedCourse->progress,
            'completed' => $purchasedCourse->completed_course,
        ]);
        
        // 🎉 ENVIAR EMAIL DE CURSO CONCLUIDO (solo la primera vez que se completa)
        if ($purchasedCourse->completed_course == 1 && !$wasCompleted) {
            try {
                $user = auth()->user();
                $course = Course::with(['category', 'user'])->find($course_id);
                
                if ($user && $course) {
                    // Calcular estadísticas del curso
                    $totalLessons = \App\Models\Lesson::whereHas('course', function($query) use ($course) {
                        $query->where('id', $course->id);
                    })->count();
                    
                    $totalModules = \App\Models\Module::where('course_id', $course->id)->count();
                    
                    // Calcular tiempo invertido (aproximado basado en la duración del curso)
                    $totalHours = floor($course->duration / 60);
                    $totalMinutes = $course->duration % 60;
                    $timeSpent = $totalHours > 0 
                        ? "{$totalHours} horas {$totalMinutes} minutos" 
                        : "{$totalMinutes} minutos";
                    
                    // Obtener cursos recomendados (3 cursos de la misma categoría)
                    $recommendedCourses = Course::where('category_id', $course->category_id)
                        ->where('id', '!=', $course->id)
                        ->where('status', 1)
                        ->inRandomOrder()
                        ->take(3)
                        ->get()
                        ->map(function($rec) {
                            return [
                                'title' => $rec->title,
                                'image' => $rec->image ?? 'https://promolider-storage-user.s3-accelerate.amazonaws.com/courses/default.jpg',
                                'category' => $rec->category->name ?? 'General',
                                'url' => url("/course/{$rec->id}")
                            ];
                        })->toArray();
                    
                    // Preparar datos para la plantilla de email
                    $templateData = [
                        // Información del estudiante
                        'student_name' => $user->name,
                        
                        // Información del curso
                        'course_title' => $course->title,
                        'course_image' => $course->image ?? 'https://promolider-storage-user.s3-accelerate.amazonaws.com/courses/default.jpg',
                        'course_category' => $course->category->name ?? 'General',
                        'instructor_name' => $course->user->name ?? 'Promolíder',
                        'completion_date' => $purchasedCourse->completed_date 
                            ? $purchasedCourse->completed_date->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                            : now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
                        'total_time_spent' => $timeSpent,
                        
                        // Estadísticas
                        'lessons_completed' => $totalLessons,
                        'modules_completed' => $totalModules,
                        
                        // Certificado (si está disponible)
                        'has_certificate' => $course->certificate ?? false,
                        'certificate_url' => url("/my-courses/{$course->id}/certificate/download"),
                        
                        // Cursos recomendados
                        'recommended_courses' => $recommendedCourses
                    ];
                    
                    $subject = "🎉 ¡Felicitaciones! Has completado el curso: {$course->title}";
                    
                    // Enviar email usando el servicio PHPMailerService
                    $phpMailerService = new \App\Services\PHPMailerService();
                    $phpMailerService->sendEmailWithTemplate(
                        $user->email,
                        $subject,
                        'emails.curso-concluido',
                        $templateData
                    );
                    
                    \Log::info("📧 Email de curso concluido enviado", [
                        'course_id' => $course_id,
                        'user_id' => $userId,
                        'user_email' => $user->email,
                        'course_title' => $course->title
                    ]);
                }
            } catch (\Exception $e) {
                // No interrumpir el flujo si falla el envío del email
                \Log::error("❌ Error enviando email de curso concluido", [
                    'course_id' => $course_id,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    
        return response()->json([
            'course_id' => $course_id,
            'progress'  => $purchasedCourse->progress,
            'completed' => $purchasedCourse->completed_course,
        ]);
    }

    public function getProgress($course_id)
    {
        $userId = auth()->id();
    
        $purchasedCourse = PurchasedCourse::where('course_id', $course_id)
            ->where('user_id', $userId)
            ->first();
    
        if (!$purchasedCourse) {
            return response()->json([
                'course_id' => $course_id,
                'progress'  => 0,
                'completed' => false
            ]);
        }
    
        return response()->json([
            'course_id' => $course_id,
            'progress'  => $purchasedCourse->progress,
            'completed' => $purchasedCourse->completed_course,
        ]);
    }

    public function isReadyToClaimCertificate($id)
    {
        $course_id = $id;
        \Log::info("Iniciando verificación de certificado", [
            'course_id' => $course_id,
            'user_id'   => auth()->id()
        ]);
    
        $lesson_seen = $this->getLessonSeen($course_id);
        \Log::info("Lecciones vistas obtenidas", ['lesson_seen' => $lesson_seen]);
    
        $course_config = CourseConfiguration::where('course_id', $course_id)->exists();
        \Log::info("¿Existe configuración del curso?", ['exists' => $course_config]);
    
        if ($course_config) {
            $conf = CourseConfiguration::where('course_id', $course_id)->first();
            \Log::info("Configuración del curso encontrada", ['conf' => $conf]);
        
            $boolean = false;
            $boolean2 = false;
        
            // Validar lecciones vistas (si no es solo exámenes)
            if ($conf->condition_to_certificate != 1) {
                \Log::info("Validando lecciones", ['validated_by' => $conf->validated_by]);
            
                switch ($conf->validated_by) {
                    case 'course':
                        $boolean = $this->validateCourseSeen($lesson_seen, $course_id);
                        break;
                    case 'module':
                        $boolean = $this->validateModuleSeen($lesson_seen, $conf);
                        break;
                    case 'lesson':
                        $boolean = $this->validateLessonSeen($lesson_seen, $conf);
                        break;
                    default:
                        $boolean = false;
                        break;
                }
                \Log::info("Resultado validación lecciones", ['boolean' => $boolean]);
            }
        
            // Validar exámenes (si no es solo lecciones)
            if ($conf->condition_to_certificate != 0) {
                $boolean2 = $this->validateExams($course_id);
                \Log::info("Resultado validación exámenes", ['boolean2' => $boolean2]);
            }
        
            // Determinar condición final
            if ($conf->condition_to_certificate == 0) {
                $evaluate_condition = $boolean; // Solo lecciones
            } else if ($conf->condition_to_certificate == 1) {
                $evaluate_condition = $boolean2; // Solo exámenes
            } else if ($conf->condition_to_certificate == 2) {
                $evaluate_condition = $boolean && $boolean2; // Ambos
            } else {
                $evaluate_condition = false;
            }
            \Log::info("Evaluación final condición certificado", [
                'evaluate_condition' => $evaluate_condition,
                'boolean'            => $boolean,
                'boolean2'           => $boolean2,
                'condition_type'     => $conf->condition_to_certificate
            ]);
        
            // Si cumple las condiciones, crear certificado y marcar como completado
            if ($evaluate_condition) {
                $user = User::findOrFail(auth()->user()->id);
                \Log::info("Usuario cumple condiciones, procesando certificado", ['user_id' => $user->id]);
            
                // Crear certificado si no existe
                $Certificate = UserCertificate::select('id')
                    ->where(['id_user' => $user->id, 'id_course' => $course_id])
                    ->get();
            
                if (count($Certificate) == 0) {
                    \Log::info("Creando certificado para usuario", [
                        'user_id' => $user->id,
                        'course_id' => $course_id
                    ]);
                    app(CertificatesController::class)->createCertificate($user, $course_id);
                } else {
                    \Log::info("Certificado ya existe", ['certificate' => $Certificate]);
                }

                app(CertificatesController::class)->screenshot($course_id, $user);
            
                // Marcar curso como completado si no está marcado
                $purchased_course = PurchasedCourse::where('course_id', $course_id)
                    ->where('user_id', $user->id)
                    ->first();
            
                if ($purchased_course && $purchased_course->completed_date == null) {
                    \Log::info("Marcando curso como completado", [
                        'user_id' => $user->id,
                        'course_id' => $course_id
                    ]);
                
                    $purchased_course->completed_course = 1;
                    $purchased_course->completed_date = now();
                    $purchased_course->update();
                }
            }
        
            return $evaluate_condition;
        } else {
            \Log::warning("Configuración de curso no encontrada", [
                'course_id' => $course_id,
                'user_id'   => auth()->id()
            ]);
        
            return false;  // No existe configuración del curso
        }
    }

    public function validateExams($course_id)
    {
        $classes = Module::join('class', 'modules.id', '=', 'class.id_modules')
            ->join('courses', 'modules.id_courses', '=', 'courses.id')
            ->where('courses.id', $course_id)
            ->select('class.id as class_id', 'class.name', 'class.slug')
            ->get();
        $modules = Course::join('modules', 'courses.id', '=', 'modules.id_courses')
            ->where('courses.id', $course_id)
            ->select('modules.id as module_id', 'modules.name', 'modules.name as slug')
            ->get();
        $approve_condition = null;
        foreach ($classes as $class) {
            if (ModelsExam::where(['lesson_id' => $class->class_id, 'status' => 1])->exists()) {
                $exam_id = ModelsExam::where('lesson_id', $class->class_id)->pluck('id');
                if (UserExamHeader::where([
                    'exam_id' => $exam_id[0],
                    'condition' => 'Approved',
                    'user_id' => auth()->user()->id
                ])->exists()) {
                    $approve_condition = true;
                } else {
                    return false;
                }
            }
        }

        foreach ($modules as $module) {
            if (ModelsExam::where(['module_id' => $module->module_id, 'status' => 1])->exists()) {
                $exam_id = ModelsExam::where('module_id', $module->module_id)->pluck('id');
                if (UserExamHeader::where([
                    'exam_id' => $exam_id[0],
                    'condition' => 'Approved',
                    'user_id' => auth()->user()->id
                ])->exists()) {
                    $approve_condition = true;
                } else {
                    return false;
                }
            }
        }

        if (ModelsExam::where(['course_id' => $course_id, 'status' => 1])->exists()) {
            $exam_id = ModelsExam::where('course_id', $course_id)->pluck('id');
            if (UserExamHeader::where([
                'exam_id' => $exam_id[0],
                'condition' => 'Approved',
                'user_id' => auth()->user()->id
            ])->exists()) {
                $approve_condition = true;
            } else {
                return false;
            }
        }
        return $approve_condition;;
    }

    //Validar si las  clases anteriores al modulo actual y de modulos anteriores han sido vistas
    public function validateLessonSeen($lesson_seen, $course_config)
    {
        $course_id =  $course_config->data['course'];
        $module_id =  $course_config->data['module'];
        $lesson_id =  $course_config->data['lesson'];
        $extra = $this->getLessonsFromCurrentModule($lesson_id,  $module_id);
        $modules = Module::where('id_courses', $course_id)->pluck('id')->toArray();
        $modules_ids_filtered = array_filter($modules, fn($xd) => $xd < $module_id);
        $lessons = $this->getRequiredLessonsIds($modules_ids_filtered);
        $full_lessons = array_merge($lessons, $extra);
        return $this->isAllLessonsSeen($lesson_seen, $full_lessons);
    }

    public function validateModuleSeen($lesson_seen, $course_config)
    {
        $course_id =  $course_config->data['course'];
        $module_id =  $course_config->data['module'];
        $modules = Module::where('id_courses', $course_id)->pluck('id')->toArray();
        $modules_ids_filtered = $this->getRequiredModulesIds($modules, $module_id);
        $lessons = $this->getRequiredLessonsIds($modules_ids_filtered);
        return $this->isAllLessonsSeen($lesson_seen, $lessons);
    }

    public function getRequiredLessonsIds($modules)
    {
        $lessons_ids = [];
        $data = [];

        foreach ($modules as $module_id) {
            $id = Clas::where('id_modules', $module_id)->pluck('id')->toArray();
            array_push($lessons_ids, $id);
        }

        # Combinar los ids de clase de las colecciones
        foreach ($lessons_ids as $record) {
            $data = array_merge($data, $record);
        }
        return $data;
    }

    public function getRequiredModulesIds($modules, $limit)
    {
        return array_filter($modules, fn($module_id) => $module_id <= $limit);
    }

    public function isAllLessonsSeen($lesson_seen, $classes)
    {
        $value = false;
        foreach ($classes as $class) {
            if (array_key_exists($class, $lesson_seen)) {
                $value = true;
            } else {
                $value = false;
                break;
            }
        }
        return $value;
    }

    public function getLessonsFromCurrentModule($lesson_id, $module_id)
    {
        //error: aqui dará error al implementar la funcionalidad de reordenamiento de clases
        $extra_lessons = Clas::where('id_modules',  $module_id)->pluck('id')->toArray();
        return array_filter($extra_lessons, fn($xd) => $xd <= $lesson_id);
    }

    public function validateCourseSeen($lesson_seen, $course_id)
    {
        $classes = Module::join('class', 'modules.id', '=', 'class.id_modules')
            ->where('modules.id_courses', $course_id)
            ->pluck('class.id');
        return $this->isAllLessonsSeen($lesson_seen, $classes);
    }

    public function fillArray($data)
    {
        $array = [];
        foreach ($data as $item) {
            array_push($array, $item[1]);
        }
        return $array;
    }

    public function getLessonSeen($course_id)
    {
        $lesson_seen = PurchasedCourse::where('course_id', '=', $course_id)
            ->where('user_id', auth()->user()->id)
            ->pluck('classes_status');
        $lesson_seen = json_decode($lesson_seen[0], TRUE);
        return $lesson_seen;
    }

    public function getLessonSeenFiltered($array, $include)
    {
        $data = [];
        foreach ($array as $item) {
            if (in_array($item[0], $include)) {
                array_push($data, $item);
            }
        }
        return $data;
    }

    public function getModuleCompletionStatus(Request $request, $course_id)
    {
        try {
            $userId = auth()->id();
            
            // Obtener el purchased_course del usuario
            $purchasedCourse = PurchasedCourse::where('course_id', $course_id)
                ->where('user_id', $userId)
                ->first();
                
            if (!$purchasedCourse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso no encontrado para el usuario',
                    'modules' => []
                ], 404);
            }
            
            // Obtener las clases completadas
            $classesStatus = json_decode($purchasedCourse->classes_status, true) ?? [];
            $completedClassIds = array_keys($classesStatus);
            
            \Log::info("Clases completadas encontradas", [
                'course_id' => $course_id,
                'user_id' => $userId,
                'completed_classes' => $completedClassIds
            ]);
            
            // Obtener todos los módulos del curso con sus clases
            $modules = Module::where('id_courses', $course_id)
                ->with(['classes' => function($query) {
                    $query->select('id', 'id_modules', 'name');
                }])
                ->get();
                
            $moduleCompletionStatus = [];
            
            foreach ($modules as $module) {
                $moduleId = $module->id;
                $moduleName = $module->name;
                
                // Obtener todas las clases de este módulo
                $moduleClassIds = $module->classes->pluck('id')->toArray();
                
                // Verificar cuáles clases del módulo han sido completadas
                $completedModuleClasses = array_intersect($completedClassIds, $moduleClassIds);
                
                // Verificar si todas las clases del módulo están completadas
                $isModuleCompleted = count($completedModuleClasses) === count($moduleClassIds);
                
                $moduleCompletionStatus[] = [
                    'module_id' => $moduleId,
                    'module_name' => $moduleName,
                    'is_completed' => $isModuleCompleted,
                    'total_classes' => count($moduleClassIds),
                    'completed_classes' => count($completedModuleClasses),
                    'completed_class_ids' => array_values($completedModuleClasses),
                    'pending_class_ids' => array_values(array_diff($moduleClassIds, $completedClassIds)),
                    'completion_percentage' => count($moduleClassIds) > 0 
                        ? round((count($completedModuleClasses) / count($moduleClassIds)) * 100, 2)
                        : 0
                ];
                
                \Log::info("Estado del módulo", [
                    'module_id' => $moduleId,
                    'module_name' => $moduleName,
                    'is_completed' => $isModuleCompleted,
                    'total_classes' => count($moduleClassIds),
                    'completed_classes' => count($completedModuleClasses)
                ]);
            }
            
            return response()->json([
                'success' => true,
                'course_id' => $course_id,
                'modules' => $moduleCompletionStatus,
                'summary' => [
                    'total_modules' => count($modules),
                    'completed_modules' => count(array_filter($moduleCompletionStatus, function($module) {
                        return $module['is_completed'];
                    }))
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error obteniendo estado de módulos", [
                'course_id' => $course_id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el estado de los módulos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Método adicional para verificar un módulo específico
    public function checkSpecificModuleCompletion(Request $request, $course_id, $module_id)
    {
        try {
            $userId = auth()->id();
            
            // Obtener el purchased_course del usuario
            $purchasedCourse = PurchasedCourse::where('course_id', $course_id)
                ->where('user_id', $userId)
                ->first();
                
            if (!$purchasedCourse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Curso no encontrado para el usuario'
                ], 404);
            }
            
            // Obtener las clases completadas
            $classesStatus = json_decode($purchasedCourse->classes_status, true) ?? [];
            $completedClassIds = array_keys($classesStatus);
            
            // Obtener el módulo específico con sus clases
            $module = Module::where('id', $module_id)
                ->where('id_courses', $course_id)
                ->with(['classes' => function($query) {
                    $query->select('id', 'id_modules', 'name');
                }])
                ->first();
                
            if (!$module) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo no encontrado en este curso'
                ], 404);
            }
            
            // Obtener todas las clases de este módulo
            $moduleClassIds = $module->classes->pluck('id')->toArray();
            
            // Verificar cuáles clases del módulo han sido completadas
            $completedModuleClasses = array_intersect($completedClassIds, $moduleClassIds);
            
            // Verificar si todas las clases del módulo están completadas
            $isModuleCompleted = count($completedModuleClasses) === count($moduleClassIds);
            
            return response()->json([
                'success' => true,
                'course_id' => $course_id,
                'module_id' => $module_id,
                'module_name' => $module->name,
                'is_completed' => $isModuleCompleted,
                'total_classes' => count($moduleClassIds),
                'completed_classes' => count($completedModuleClasses),
                'completed_class_ids' => array_values($completedModuleClasses),
                'pending_class_ids' => array_values(array_diff($moduleClassIds, $completedClassIds)),
                'completion_percentage' => count($moduleClassIds) > 0 
                    ? round((count($completedModuleClasses) / count($moduleClassIds)) * 100, 2)
                    : 0,
                'message' => $isModuleCompleted 
                    ? "El módulo '{$module->name}' ha sido completado" 
                    : "El módulo '{$module->name}' no se ha completado"
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error verificando módulo específico", [
                'course_id' => $course_id,
                'module_id' => $module_id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el estado del módulo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getCertificateConfiguration(Request $request)
    {
        //correccion
        $courseConfiguration = CourseConfiguration::join('courses', 'course_configuration.course_id', '=', 'courses.id')
            ->where('course_id', $request->course_id)
            ->select('course_configuration.*', 'courses.title', 'courses.url_portada')
            ->first();
        $courseConfiguration->url_portada = config('global_variables.storage_domain') . '/' . $courseConfiguration->url_portada;
        return $courseConfiguration;
    }
}
