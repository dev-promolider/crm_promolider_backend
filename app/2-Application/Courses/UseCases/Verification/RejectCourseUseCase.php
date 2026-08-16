<?php

namespace Promolider\Application\Courses\UseCases\Verification;

use App\Models\Course;
use App\Models\CourseObservation;
use App\Models\Notifications;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Module;
use App\Models\Clas;
use Promolider\Infrastructure\Marketing\Out\Services\PHPMailerService;

class RejectCourseUseCase
{
    public function execute($courseId, $observationText, $analystId)
    {
        if (!$analystId) {
            throw new \Exception('No autenticado. Inicie sesión para enviar observaciones.');
        }

        try {
            DB::beginTransaction();

            $course = Course::findOrFail($courseId);
            $course->status = 3;
            $course->update();

            $title = 'Curso con observaciones';
            $body = "$course->title tiene observaciones.";
            $this->createNotification($course->user_id, $title, $body);

            if ($observationText) {
                $firstModule = Module::where('id_courses', $courseId)->first();
                $idClass = null;
                if ($firstModule) {
                    $firstClass = Clas::where('id_modules', $firstModule->id)->first();
                    if ($firstClass) {
                        $idClass = $firstClass->id;
                    }
                }
                if (!$idClass) {
                    throw new \Exception('No se encontró ninguna clase asociada al curso para guardar la observación.');
                }

                $courseObservation = new CourseObservation();
                $courseObservation->id_courses = $courseId;
                $courseObservation->id_analyst = $analystId;
                $courseObservation->id_class = $idClass;
                $courseObservation->observation = $observationText;
                $courseObservation->status = 0;
                $courseObservation->save();
            }

            $this->sendRejectionEmail($course, $observationText);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error rejecting course: " . $e->getMessage());
            throw $e;
        }
    }

    private function createNotification($userId, $title, $body)
    {
        $notification = new Notifications();
        $notification->id_generator = 1; // Assuming admin or system
        $notification->id_receiver = $userId;
        $notification->title = $title;
        $notification->body = $body;
        $notification->type = 1;
        $notification->save();
    }

    private function sendRejectionEmail($course, $observationText)
    {
        $user = User::find($course->user_id);
        if (!$user) return;

        try {
            $phpMailerService = new PHPMailerService();
            $category = \App\Models\Category::find($course->id_categories);
            $level = DB::table('course_level')->where('id', $course->course_level_id)->first();

            $courseData = [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'price' => $course->price,
                'currency' => $course->currency ?? 'soles',
                'is_free' => $course->price <= 0,
                'category' => $category->name ?? 'Sin categoría',
                'level' => $level->name ?? 'Sin nivel',
                'months' => $course->months,
                'course_time' => $course->course_time ?? 0,
                'certificate' => $course->certificate == 1,
                'course_about' => $course->course_about,
                'will_learn' => $course->will_learn,
                'prev_knowledge' => $course->prev_knowledge,
                'course_for' => $course->course_for,
                'cover_image_url' => $course->url_portada ?? null
            ];

            $instructorData = [
                'name' => $user->name ?? $user->username,
                'email' => $user->email,
                'phone' => $user->phone ?? 'No especificado'
            ];

            $templateData = [
                'course' => $courseData,
                'instructor' => $instructorData,
                'timestamp' => now()->format('d/m/Y H:i:s'),
                'admin_url' => url('/admin/courses/' . $course->id),
                'observation' => $observationText // Pass the observation text to the template if it supports it
            ];

            $phpMailerService->sendEmailWithTemplate(
                $user->email,
                'Curso con Observaciones: ' . $courseData['title'],
                'emails.new-course-notification', // Adjust template name if needed
                $templateData,
                'Promolíder - Curso con Observaciones'
            );
        } catch (\Exception $e) {
            Log::error('No se pudo enviar email de curso rechazado: ' . $e->getMessage());
        }
    }
}
