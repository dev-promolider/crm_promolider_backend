<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Domain\Infoproducts\Ports\Out\ClassVideoStorageInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use RuntimeException;

final class GenerateClassVideoUploadUrlUseCase
{
    public function __construct(
        private ModuleClassRepositoryInterface $moduleClassRepository,
        private ClassVideoStorageInterface $videoStorage
    ) {
    }

    public function execute(
        int $userId,
        int $classId,
        string $filename
    ): array {
        $context = $this->moduleClassRepository
            ->findClassContext($classId);

        if ($context === null) {
            throw new RuntimeException(
                'La clase indicada no existe.'
            );
        }

        if ($context['user_id'] !== $userId) {
            throw new RuntimeException(
                'No tienes autorización para modificar esta clase.'
            );
        }

        $uploadData = $this->videoStorage
            ->createPresignedUploadUrl(
                userId: $userId,
                courseId: $context['course_id'],
                classId: $classId,
                filename: $filename
            );

        $this->moduleClassRepository->saveVideoInformation(
            classId: $classId,
            filename: $uploadData['filename'],
            path: $uploadData['path']
        );

        return [
            'upload_url' => $uploadData['upload_url'],
            'filename' => $uploadData['filename'],
            'path' => $uploadData['path'],
        ];
    }
}
