<?php
namespace App\Services\Infoproduct\Book;

use App\Models\Course;
use App\Models\Notifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\PHPMailerService;

class ReviewBookService
{
    public function __construct(
        private PHPMailerService $mailerService
    ){}

    public function review(Course $course, array $data)
    {
        $reviewStatus = $data['status'];
        $courseStatus = $reviewStatus === 'approved' ? 2 : 3; // 2 para aprobado, 3 para rechazado

        DB::transaction(function () use ($course, $data, $reviewStatus, $courseStatus) {
            // Solo crea la observación si el revisor proporcionó una
            if (!empty($data['observations'])) {
                $course->bookObservations()->create([
                    'analyst_id' => auth()->id(),
                    'observations' => $data['observations'],
                    'status' => $reviewStatus
                ]);
            }

            // Actualiza el estado del libro
            $course->update([
                'status' => $courseStatus
            ]);
        });

        //--------------- Envío de mail ---------------------
        $userEmail = $course->user->email;
        $subject = $reviewStatus === 'approved' ? 'Tu libro ha sido aprobado' : 'Tu libro tiene observaciones';
        $template = $reviewStatus === 'approved' ? 'emails.infoproducts.books.book-status-approved' : 'emails.infoproducts.books.book-status-observations';
        $templateData = [
            'course' => $course,
            'instructor' => [
                'name' => $course->user->name,
                'email' => $course->user->email,
                'phone' => $course->user && isset($course->user->phone) ? $course->user->phone : 'No especificado'
            ],
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'admin_url' => url('/admin/courses/' . $course->id),
            'observaciones' => !empty($data['observations']) ? $data['observations'] : 'No se registraron observaciones para este libro.'
        ];

        try {
            $this->mailerService->sendEmailWithTemplate($userEmail, $subject, $template, $templateData, 'Promolider');
        } catch (\Exception $e) {
            // Loguear el error pero no interrumpir el proceso de revisión
            Log::error('Error enviando email de revisión de libro: ' . $e->getMessage(), [
                'course_id' => $course->id,
                'user_email' => $userEmail
            ]);
        }

        //------------------- Envío de notificación al usuario ------------------------
        Notifications::create([
            'id_generator' => $course->user_id,
            'id_receiver' => $course->user_id,
            'title' => $reviewStatus === 'approved' ? 'Libro aprobado' : 'Libro con observaciones',
            'body' => $reviewStatus === 'approved' ? '¡Felicidades! Tu libro "' . $course->name . '" ha sido aprobado.' : 'Tu libro "' . $course->name . '" tiene observaciones. Por favor revisa tu correo para más detalles o ingresa al siguiente enlace: ' . url('/course/' . $course->id . '/book-content'),
            'type' => 3,
        ]);
    }
}
