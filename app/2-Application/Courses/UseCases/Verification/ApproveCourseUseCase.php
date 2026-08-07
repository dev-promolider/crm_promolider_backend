<?php

namespace Promolider\Application\Courses\UseCases\Verification;

use App\Models\Course;
use App\Models\User;
use App\Models\Notifications;
use App\Models\Category;
use App\Models\CourseLevel;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApproveCourseUseCase
{
    public function execute($courseId)
    {
        try {
            DB::beginTransaction();
            $course = Course::findOrFail($courseId);
            $course->status = 2;
            $course->update();

            $title = 'Infoproducto aprobado';
            $body = "$course->title fue aprobado!";
            $this->createNotification($course->user_id, $title, $body);

            $this->sendApprovalEmail($course);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error approving course: " . $e->getMessage());
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

    private function sendApprovalEmail($course)
    {
        $user = User::find($course->user_id);
        if (!$user) return;

        try {
            $phpMailerService = new PHPMailerService();
            $category = Category::find($course->id_categories);
            $level = CourseLevel::find($course->course_level_id);

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
                'admin_url' => url('/admin/courses/' . $course->id)
            ];

            $phpMailerService->sendEmailWithTemplate(
                $user->email,
                'Curso Aprobado: ' . $courseData['title'],
                'emails.new-course-notification', // Adjust template name if needed
                $templateData,
                'Promolíder - Curso Aprobado'
            );
        } catch (\Exception $e) {
            Log::error('No se pudo enviar email de curso aprobado: ' . $e->getMessage());
        }
    }
}
