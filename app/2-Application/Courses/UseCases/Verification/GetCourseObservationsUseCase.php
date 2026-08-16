<?php

namespace Promolider\Application\Courses\UseCases\Verification;

use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Exception;

class GetCourseObservationsUseCase
{
    public function execute($courseId, $userId)
    {
        $course = Course::find($courseId);

        if (!$course) {
            throw new Exception("Curso no encontrado.", 404);
        }

        // Validate that the course belongs to the user requesting it
        if ($course->user_id !== $userId) {
            throw new Exception("No tienes permiso para ver las observaciones de este curso.", 403);
        }

        // Get the latest observation
        $observation = DB::table('course_observations')
            ->where('id_courses', $courseId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$observation) {
            return null;
        }

        return $observation;
    }
}
