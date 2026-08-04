<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

//use App\Services\PHPMailerService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use Promolider\Infrastructure\Infoproducts\Out\Services\NotificationService;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;

final class SubmitCourseForReviewUseCase
{
    private const PRODUCT_TYPE_BOOK = 2;

    private const STATUS_DRAFT = 0;
    private const STATUS_REJECTED = 3;
    private const STATUS_PENDING_CHANGES = 4;
    private const STATUS_PENDING_REVIEW = 1;

    private const TEMPLATE_CONFIGURATION = 1;
    private const SIGNATURE_CONFIGURATION = 2;

    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        //private PHPMailerService $mailerService,
        private NotificationService $notificationService
    ) {
    }

    public function execute(
        int $userId,
        int $courseId
    ): string {
        $course = $this->courseRepository
            ->findCourseForReview($courseId);

        if ($course === null) {
            throw new \Exception(
                'El curso no existe o no está disponible para revisión.'
            );
        }

        if ((int) $course['user_id'] !== $userId) {
            throw new AuthorizationException(
                'No tienes autorización para enviar este curso a revisión.'
            );
        }

        $isBook = (int) $course['product_type_id']
            === self::PRODUCT_TYPE_BOOK;

        $result = $this->courseRepository->transaction(
            function () use ($course, $courseId, $isBook) {
                if ($isBook) {
                    $hasFiles = $this->courseRepository
                        ->hasBookFiles($courseId);

                    if (!$hasFiles) {
                        return 'empty_files';
                    }
                } else {
                    $hasModules = $this->courseRepository
                        ->hasModules($courseId);

                    if (!$hasModules) {
                        return 'empty';
                    }
                }

                if ((int) $course['certificate'] === 1) {
                    $configurationResult = $this
                        ->validateCertificateConfiguration($course);

                    if ($configurationResult !== null) {
                        return $configurationResult;
                    }
                }

                $allowedStatuses = [
                    self::STATUS_DRAFT,
                    self::STATUS_REJECTED,
                    self::STATUS_PENDING_CHANGES,
                ];

                if (
                    !in_array(
                        (int) $course['status'],
                        $allowedStatuses,
                        true
                    )
                ) {
                    return 'request';
                }

                $this->courseRepository->updateStatus(
                    courseId: $courseId,
                    status: self::STATUS_PENDING_REVIEW
                );

                return 'ok';
            }
        );

        if ($result === 'ok') {
            $this->notifyAdmins($course);

            if (!$isBook) {
                // $this->sendPendingReviewEmails($course);
            }
        }

        return $result;
    }

    private function validateCertificateConfiguration(
        array $course
    ): ?string {
        $courseIsConfigured = $this->courseRepository
            ->hasCourseConfiguration((int) $course['id']);

        if (!$courseIsConfigured) {
            return 'misconfigured';
        }

        $hasSignature = $this->courseRepository
            ->hasUserConfiguration(
                userId: (int) $course['user_id'],
                configurationId: self::SIGNATURE_CONFIGURATION
            );

        $hasTemplate = $this->courseRepository
            ->hasUserConfiguration(
                userId: (int) $course['user_id'],
                configurationId: self::TEMPLATE_CONFIGURATION
            );

        if (!$hasSignature || !$hasTemplate) {
            return 'signaturetemplate';
        }

        return null;
    }

    private function notifyAdmins(array $course): void
    {
        $adminIds = $this->courseRepository->findAdminIds();

        if (empty($adminIds)) {
            return;
        }

        try {
            $this->notificationService->sendMany(
                generatorId: (int) $course['user_id'],
                receiverIds: $adminIds,
                title: 'Curso pendiente por revisar',
                body: "Tiene pendiente revisar {$course['title']}",
                type: 3
            );
        } catch (\Throwable $exception) {
            Log::error(
                'No se pudieron crear las notificaciones para los administradores.',
                [
                    'course_id' => $course['id'],
                    'admin_ids' => $adminIds,
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }

    private function sendPendingReviewEmails(array $course): void
    {
        $courseData = [
            'id' => $course['id'],
            'title' => $course['title'],
            'description' => $course['description'],
            'price' => $course['price'],
            'currency' => $course['currency'] ?? 'soles',
            'is_free' => (float) $course['price'] <= 0,
            'category' => $course['category'],
            'level' => $course['level'],
            'months' => $course['months'],
            'course_time' => $course['course_time'] ?? 0,
            'certificate' => (int) $course['certificate'] === 1,
            'course_about' => $course['course_about'] ?? '',
            'will_learn' => $course['will_learn'] ?? '',
            'prev_knowledge' => $course['prev_knowledge'] ?? '',
            'course_for' => $course['course_for'] ?? '',
            'cover_image_url' => $course['cover_image_url'],
        ];

        $instructorData = [
            'name' => $course['instructor_name'],
            'email' => $course['instructor_email'],
            'phone' => $course['instructor_phone'],
        ];

        $templateData = [
            'course' => $courseData,
            'instructor' => $instructorData,
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'admin_url' => url(
                '/admin/courses/' . $course['id']
            ),
        ];

        try {
            /* $this->mailerService->sendEmailWithTemplate(
                'soporte@promolider.info',
                '🕒 Curso pendiente de revisión: ' . $course['title'],
                'emails.course-status-pending',
                $templateData,
                'Promolíder - Estado de Curso'
            ); */
        } catch (\Throwable $exception) {
            Log::error(
                'Error enviando correo de revisión a soporte.',
                [
                    'course_id' => $course['id'],
                    'message' => $exception->getMessage(),
                ]
            );
        }

        try {
            /* $this->mailerService->sendEmailWithTemplate(
                $course['instructor_email'],
                'Tu curso está pendiente de revisión: '
                    . $course['title'],
                'emails.course-status-pending',
                $templateData,
                'Promolíder - Estado de Curso'
            ); */
        } catch (\Throwable $exception) {
            Log::error(
                'Error enviando correo de revisión al usuario.',
                [
                    'course_id' => $course['id'],
                    'user_email' => $course['instructor_email'],
                    'message' => $exception->getMessage(),
                ]
            );
        }
    }
}
