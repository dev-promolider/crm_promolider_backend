<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Models\Clas;
use App\Models\Course;
use App\Models\CourseConfiguration;
use App\Models\Exam;
use App\Models\Module;
use App\Models\Option;
use App\Models\PurchasedCourse;
use App\Models\UserCourseProgress;
use App\Models\UserExamHeader;
use App\Models\UserLessonProgress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrLearningController extends Controller
{
    /**
     * GET /api/v1/course/temary/get-all-class/{id}
     */
    public function getTemary($id)
    {
        $course = Course::select('id', 'title')->where('id', $id)->first();
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Curso no encontrado'], 404);
        }

        $modules = Module::where('id_courses', $id)->get();
        foreach ($modules as $module) {
            $module->lessons = Clas::where('id_modules', $module->id)
                ->where('status', 2)
                ->get();
        }

        $course->modules = $modules;

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $course,
        ]);
    }

    /**
     * GET /api/v1/purchased/show
     */
    public function showPurchased(Request $request)
    {
        $purchasedCourse = PurchasedCourse::where('user_id', auth()->id())
            ->where('course_id', $request->course_id)
            ->first();

        if (!$purchasedCourse) {
            return response()->json(['error' => 'No se encontró el curso para este usuario'], 404);
        }

        $dataset = json_decode($purchasedCourse->classes_status, true);
        if (!is_array($dataset)) {
            $dataset = [];
        }

        $result1 = [];
        $result2 = [];
        foreach ($dataset as $data) {
            if (isset($data[0])) {
                $result1[] = $data[0];
            }
            if (isset($data[1])) {
                $result2[] = $data[1];
            }
        }

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $result1,
            'data2' => $result2,
        ]);
    }

    /**
     * PATCH /api/v1/purchased/save-class-seen
     */
    public function saveClassSeen(Request $request)
    {
        if (!empty($request->course_id) && !empty($request->class_id)) {
            $purchased = PurchasedCourse::where('course_id', $request->course_id)
                ->where('user_id', auth()->id())
                ->first();

            if (!$purchased) {
                return response()->json(['status' => 'error', 'message' => 'Curso no comprado'], 404);
            }

            $purchased->display_time = $request->display_time;
            $purchased->last_class_reprod = $request->class_id;

            $object = json_decode($purchased->classes_status, true) ?? [];
            $object[$request->class_id] = [
                'time' => $request->display_time,
            ];
            $purchased->classes_status = json_encode($object);
            $purchased->save();

            return response()->json([
                'status' => 'ok',
                'message' => '',
                'data' => $purchased,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Faltan parámetros'], 400);
    }

    /**
     * GET /api/v1/purchased/show-class-seen
     */
    public function showClassSeen(Request $request)
    {
        $idLastClassPlay = PurchasedCourse::select('last_class_reprod', 'display_time')
            ->where('course_id', $request->course_id)
            ->where('user_id', auth()->id())
            ->first();

        if ($idLastClassPlay == null) {
            return response()->json(['status' => 'ok', 'data' => 'no existe']);
        }

        $lastClassPlay = Clas::select('id', 'name')->where('id', $idLastClassPlay->last_class_reprod)->first();
        if ($lastClassPlay) {
            $lastClassPlay->display_time = $idLastClassPlay->display_time;
        }

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $lastClassPlay,
        ]);
    }

    /**
     * GET /api/v1/course/{courseId}/progress
     */
    public function getProgress($courseId)
    {
        $userId = auth()->id();
        $progress = UserCourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->value('progress') ?? 0;

        return response()->json([
            'progress' => (int) $progress,
        ]);
    }

    /**
     * GET /api/v1/course/{courseId}/completed-lessons
     */
    public function getCompletedLessons($courseId)
    {
        $userId = auth()->id();
        $completedLessons = UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->pluck('lesson_id')
            ->toArray();

        return response()->json([
            'completed_lessons' => $completedLessons,
        ]);
    }

    /**
     * POST /api/v1/course/{courseId}/complete-lesson
     */
    public function completeLesson(Request $request, $courseId)
    {
        $userId = auth()->id();
        $lessonId = $request->lesson_id;

        UserLessonProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId, 'lesson_id' => $lessonId],
            ['completed' => true, 'completed_at' => now()]
        );

        $totalLessons = Clas::join('modules', 'class.id_modules', '=', 'modules.id')
            ->where('modules.id_courses', $courseId)
            ->where('class.status', 2)
            ->count();

        $completedLessons = UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->count();

        $progress = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        UserCourseProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['progress' => $progress, 'updated_at' => now()]
        );

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
        ]);
    }

    /**
     * POST /api/v1/course/{courseId}/update-progress
     */
    public function updateProgress(Request $request, $courseId)
    {
        $userId = auth()->id();

        // CRM-21: Recalcular el progreso de forma segura en el backend a partir de las lecciones completadas
        $totalLessons = Clas::join('modules', 'class.id_modules', '=', 'modules.id')
            ->where('modules.id_courses', $courseId)
            ->where('class.status', 2)
            ->count();

        $completedLessons = UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->count();

        $progress = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        UserCourseProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['progress' => $progress, 'updated_at' => now()]
        );

        return response()->json([
            'success' => true,
            'course_id' => $courseId,
            'progress' => $progress,
            'completed' => $progress >= 100,
            'message' => $progress >= 100 ? 'Curso completado' : 'Progreso actualizado',
        ]);
    }

    /**
     * POST /api/v1/course/exam/active
     */
    public function getExamActive(Request $request)
    {
        $examId = $request->input('exam_id');
        $exam = Exam::with('questions.options')->find($examId);

        if (!$exam) {
            return response()->json(['status' => 'error', 'message' => 'Examen no encontrado'], 404);
        }

        return response()->json([
            'status' => 'ok',
            'data' => $exam,
        ]);
    }

    /**
     * POST /api/v1/course/exam/calification
     */
    public function getExamCalification(Request $request)
    {
        $examId = $request->input('exam_id');
        $answers = $request->input('answers', []);
        $userId = auth()->id();

        $correct = 0;
        $total = count($answers);

        foreach ($answers as $ans) {
            $questionId = $ans['question_id'] ?? null;
            $optionId = $ans['option_id'] ?? null;

            if ($questionId && $optionId) {
                $isCorrect = Option::where('id', $optionId)->where('question_id', $questionId)->where('is_correct', 1)->exists();
                if ($isCorrect) {
                    $correct++;
                }
            }
        }

        $calification = $total > 0 ? round(($correct / $total) * 20, 2) : 0;
        $approved = $calification >= 14;

        UserExamHeader::create([
            'user_id' => $userId,
            'exam_id' => $examId,
            'calification' => $calification,
            'status' => $approved ? 1 : 0,
        ]);

        return response()->json([
            'status' => 'ok',
            'calification' => $calification,
            'approved' => $approved,
            'message' => $approved ? 'Examen aprobado con éxito' : 'Examen no aprobado',
        ]);
    }

    /**
     * GET /api/v1/course/certificate/check/{id}
     */
    public function checkCertificate($id)
    {
        $userId = auth()->id();
        $courseConfig = CourseConfiguration::where('course_id', $id)->first();
        $typeCertificate = $courseConfig->type_certificate ?? 1;

        $progress = UserCourseProgress::where('user_id', $userId)->where('course_id', $id)->value('progress') ?? 0;
        $ready = $progress >= 100;

        return response()->json([
            'ready' => $ready,
            'type_certificate' => $typeCertificate == 1 ? 'Curso Completado' : 'Módulos Aprobados',
            'certificate_id' => $id,
        ]);
    }

    /**
     * GET /api/v1/certificate/download/{course_id}
     */
    public function downloadCertificate($course_id)
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'Generando certificado...',
            'download_url' => url("/api/v1/certificate/pdf/{$course_id}"),
        ]);
    }
}
