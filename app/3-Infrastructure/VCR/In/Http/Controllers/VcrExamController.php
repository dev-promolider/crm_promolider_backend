<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Models\ClassroomPointDetail;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\UserClassroomPoint;
use App\Models\UserExamHeader;
use App\Models\UserQuestionAnswer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrExamController extends Controller
{
    /**
     * POST /api/v1/exam/active
     */
    public function active(Request $request)
    {
        $userId = auth()->id();
        $examType = $request->input('exam_type', 'course');
        $idType = $request->input('id_type');

        $query = Exam::where('status', 1);

        if ($examType === 'course') {
            $query->where('course_id', $idType);
        } elseif ($examType === 'module') {
            $query->where('module_id', $idType);
        } elseif ($examType === 'class') {
            $query->where('lesson_id', $idType);
        }

        $exam = $query->first();

        if (!$exam) {
            return response()->json(['status' => 'error', 'message' => 'No hay examen activo configurado'], 404);
        }

        $approved = UserExamHeader::where('user_id', $userId)
            ->where('exam_id', $exam->id)
            ->where('condition', 'Approved')
            ->exists();

        if ($approved) {
            return response()->json('El usuario ya aprobó el examen');
        }

        $waiting = UserExamHeader::where('user_id', $userId)
            ->where('exam_id', $exam->id)
            ->where('condition', 'Waiting')
            ->exists();

        if ($waiting) {
            return response()->json('examen en espera');
        }

        $disapprovedCount = UserExamHeader::where('user_id', $userId)
            ->where('exam_id', $exam->id)
            ->where('condition', 'Disapproved')
            ->count();

        if ($disapprovedCount >= 3) {
            return response()->json('límite de intentos alcanzado');
        }

        return response()->json($exam->id);
    }

    /**
     * POST /api/v1/exam/module/active
     */
    public function moduleActive(Request $request)
    {
        $userId = auth()->id();
        $courseId = $request->input('id_course');

        $courseExam = Exam::where('course_id', $courseId)->where('status', 1)->first();

        $moduleExams = Exam::join('modules', 'modules.id', '=', 'exams.module_id')
            ->where('modules.id_courses', $courseId)
            ->where('exams.status', 1)
            ->select('exams.id', 'exams.title', 'exams.module_id')
            ->get();

        return response()->json([
            'course_exam' => $courseExam ? ['id' => $courseExam->id, 'title' => $courseExam->title] : null,
            'module_exams' => $moduleExams,
        ]);
    }

    /**
     * POST /api/v1/exam/answers
     */
    public function answers(Request $request)
    {
        $userId = auth()->id();
        $examId = $request->input('id_exam');
        $secondsUsed = $request->input('seconds_used', 0);
        $answers = $request->input('answers', []);

        $exam = Exam::find($examId);
        if (!$exam) {
            return response()->json(['status' => 'error', 'message' => 'Examen no encontrado'], 404);
        }

        $totalScore = 0;
        $hasOpenQuestion = false;

        $header = UserExamHeader::create([
            'user_id' => $userId,
            'exam_id' => $examId,
            'productor_id' => $exam->productor_id ?? 0,
            'rate' => 0,
            'condition' => 'Waiting',
            'status' => 1,
        ]);

        foreach ($answers as $ans) {
            $qId = $ans['question_id'] ?? null;
            $selectedOpt = $ans['selected_option'] ?? null;

            if ($qId) {
                $question = ExamQuestion::find($qId);
                if ($question) {
                    if ($question->question_type_id == 4) {
                        $hasOpenQuestion = true;
                    } else {
                        if ($question->correct == $selectedOpt) {
                            $totalScore += ($question->points ?? 1);
                        }
                    }

                    UserQuestionAnswer::create([
                        'user_exam_header_id' => $header->id,
                        'exam_question_id' => $qId,
                        'user_answer' => (string) $selectedOpt,
                    ]);
                }
            }
        }

        if ($hasOpenQuestion) {
            $condition = 'Waiting';
        } else {
            $minScore = $exam->min_passing_score ?? 14;
            $condition = ($totalScore >= $minScore) ? 'Approved' : 'Disapproved';
        }

        $header->rate = $totalScore;
        $header->condition = $condition;
        $header->save();

        $pointsGained = 0;
        if ($condition === 'Approved') {
            $pointsGained = 50;
            $userPoints = UserClassroomPoint::firstOrCreate(['id_user' => $userId], ['total_points' => 0]);
            $userPoints->total_points += $pointsGained;
            $userPoints->save();

            ClassroomPointDetail::create([
                'id_user_classroom_points' => $userPoints->id,
                'increment_points' => $pointsGained,
                'description' => 'Puntos por aprobar examen: ' . $exam->title,
            ]);
        }

        return response()->json([
            'rate' => (float) $totalScore,
            'points_gained' => $pointsGained,
            'condition' => $condition,
        ]);
    }

    /**
     * POST /api/v1/exam/calification
     */
    public function calification(Request $request)
    {
        // CRM-08: Prevenir IDOR. Solo administradores pueden ver calificaciones de terceros.
        $user = auth()->user();
        $requestedUserId = $request->input('user_id');
        $isAdmin = $user && (
            (method_exists($user, 'hasRole') && $user->hasRole('Admin'))
            || ($user->id_account_type ?? null) == 1
        );

        $userId = ($requestedUserId && $isAdmin) ? $requestedUserId : auth()->id();
        $lessonId = $request->input('lesson_id');
        $courseId = $request->input('course_id');

        $query = Exam::where('status', 1);
        if ($lessonId) {
            $query->where('lesson_id', $lessonId);
        } elseif ($courseId) {
            $query->where('course_id', $courseId);
        }

        $exam = $query->first();

        if (!$exam) {
            return response()->json(['status' => 'error', 'message' => 'No hay examen configurado'], 404);
        }

        $header = UserExamHeader::where('user_id', $userId)
            ->where('exam_id', $exam->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$header) {
            return response()->json([
                'title' => $exam->title,
                'max_score' => $exam->max_score ?? 20,
                'calification' => 0,
                'status' => 'NotTaken',
                'message' => 'Examen no realizado aún',
            ]);
        }

        return response()->json([
            'title' => $exam->title,
            'teacher' => 'Docente',
            'max_score' => $exam->max_score ?? 20,
            'calification' => (float) $header->rate,
            'status' => $header->condition,
            'message' => 'Examen realizado',
        ]);
    }

    /**
     * GET /api/v1/exam/isconfig/{id}
     */
    public function isConfigured($id)
    {
        $hasExam = Exam::where('course_id', $id)->orWhere('module_id', $id)->where('status', 1)->exists();

        return response()->json([
            'status' => 'ok',
            'is_configured' => $hasExam,
        ]);
    }

    /**
     * GET /api/v1/exam/daily
     */
    public function dailyQuestion()
    {
        return response()->json([
            'status' => 'ok',
            'question' => [
                'id' => 1,
                'title' => '¿Cuál es la clave para la gestión efectiva del tiempo?',
                'options' => [
                    ['id' => 1, 'text' => 'Priorizar tareas según importancia y urgencia'],
                    ['id' => 2, 'text' => 'Trabajar sin descansar'],
                    ['id' => 3, 'text' => 'Hacer múltiples tareas complejas a la vez'],
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/exam/daily/points
     */
    public function dailyPoints(Request $request)
    {
        $userId = auth()->id();
        $optionId = $request->input('option_id');

        $userPoints = UserClassroomPoint::firstOrCreate(['id_user' => $userId], ['total_points' => 0]);

        // CRM-07: Control anti-repetición diario por usuario
        $alreadyAnsweredToday = ClassroomPointDetail::where('id_user_classroom_points', $userPoints->id)
            ->where('description', 'like', 'Puntos por pregunta diaria%')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyAnsweredToday) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ya respondiste la pregunta diaria del día de hoy',
            ], 400);
        }

        $isCorrect = ($optionId == 1);
        $points = $isCorrect ? 10 : 0;

        if ($isCorrect) {
            $userPoints->total_points += $points;
            $userPoints->save();

            ClassroomPointDetail::create([
                'id_user_classroom_points' => $userPoints->id,
                'increment_points' => $points,
                'description' => 'Puntos por pregunta diaria (' . now()->toDateString() . ')',
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'correct' => $isCorrect,
            'points_gained' => $points,
        ]);
    }
}
