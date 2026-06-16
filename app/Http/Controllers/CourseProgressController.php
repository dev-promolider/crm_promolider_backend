<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserCourseProgress;
use App\Models\UserLessonProgress;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseProgressController extends Controller
{
    public function getCompletedLessons($courseId)
    {
        $userId = Auth::id();
        Log::info("getCompletedLessons() llamado", ['user_id' => $userId, 'course_id' => $courseId]);

        $completedLessons = UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->pluck('lesson_id')
            ->toArray();

        Log::info("Lecciones completadas recuperadas", [
            'user_id' => $userId,
            'course_id' => $courseId,
            'completed_lessons' => $completedLessons
        ]);

        return response()->json([
            'completed_lessons' => $completedLessons
        ]);
    }

    public function updateCourseProgress(Request $request, $courseId)
    {
        try {
            $userId = Auth::id();
            $progress = $request->input('progress', 0);
        
            Log::info("updateCourseProgress() llamado", [
                'user_id' => $userId,
                'course_id' => $courseId,
                'progress' => $progress
            ]);
        
            // Actualizar o crear el progreso del curso
            $courseProgress = UserCourseProgress::updateOrCreate(
                ['user_id' => $userId, 'course_id' => $courseId],
                ['progress' => $progress, 'updated_at' => now()]
            );
        
            // Verificar si el curso se completó (100%)
            $completed = $progress >= 100;
        
            Log::info("Progreso del curso actualizado", [
                'user_id' => $userId,
                'course_id' => $courseId,
                'progress' => $progress,
                'completed' => $completed
            ]);
        
            return response()->json([
                'success' => true,
                'course_id' => $courseId,
                'progress' => $progress,
                'completed' => $completed,
                'message' => $completed ? 'Curso completado' : 'Progreso actualizado'
            ]);
        
        } catch (\Exception $e) {
            Log::error("Error al actualizar progreso del curso", [
                'course_id' => $courseId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
        
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el progreso',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProgress($courseId)
    {
        $userId = Auth::id();
        Log::info("getProgress() llamado", ['user_id' => $userId, 'course_id' => $courseId]);

        $progress = UserCourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->value('progress') ?? 0;

        Log::info("Progreso recuperado", [
            'user_id' => $userId,
            'course_id' => $courseId,
            'progress' => $progress
        ]);

        return response()->json([
            'progress' => $progress
        ]);
    }

    public function completeLesson(Request $request, $courseId)
    {
        $userId = Auth::id();
        $lessonId = $request->lesson_id;

        Log::info("completeLesson() llamado", [
            'user_id' => $userId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId
        ]);

        // Marcar lección como completada
        UserLessonProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId, 'lesson_id' => $lessonId],
            ['completed' => true, 'completed_at' => now()]
        );

        Log::info("Lección marcada como completada", [
            'user_id' => $userId,
            'course_id' => $courseId,
            'lesson_id' => $lessonId
        ]);

        // Calcular y actualizar progreso del curso
        $totalLessons = $this->getTotalLessonsInCourse($courseId);
        $completedLessons = UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->count();

        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        Log::info("Progreso calculado", [
            'user_id' => $userId,
            'course_id' => $courseId,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'progress' => $progress
        ]);

        UserCourseProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            ['progress' => $progress, 'updated_at' => now()]
        );

        Log::info("Progreso actualizado en DB", [
            'user_id' => $userId,
            'course_id' => $courseId,
            'progress' => $progress
        ]);

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons
        ]);
    }

    public function syncProgress($courseId)
    {
        $userId = Auth::id();
        Log::info("syncProgress() llamado", ['user_id' => $userId, 'course_id' => $courseId]);

        $completedLessons = UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->pluck('lesson_id')
            ->toArray();

        $progress = UserCourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->value('progress') ?? 0;

        Log::info("Progreso sincronizado", [
            'user_id' => $userId,
            'course_id' => $courseId,
            'completed_lessons' => $completedLessons,
            'progress' => $progress
        ]);

        return response()->json([
            'completed_lessons' => $completedLessons,
            'progress' => $progress
        ]);
    }

    private function getTotalLessonsInCourse($courseId)
    {
        Log::info("getTotalLessonsInCourse() llamado", ['course_id' => $courseId]);

        $totalLessons = Course::find($courseId)
            ->modules()
            ->withCount('lessons')
            ->get()
            ->sum('lessons_count');

        Log::info("Total de lecciones en curso calculado", [
            'course_id' => $courseId,
            'total_lessons' => $totalLessons
        ]);

        return $totalLessons;
    }
}