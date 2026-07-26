<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use Promolider\Infrastructure\Infoproducts\Out\Storage\ClassResourceService;
use RuntimeException;

final class UpdateModuleClassUseCase
{
    private const STATUS_PENDING_REVIEW = 4;

    public function __construct(
        private ModuleClassRepositoryInterface $repository,
        private ClassResourceService $classResourceService
    ) {
    }

    public function execute(
        int $userId,
        int $classId,
        array $data,
        array $resources = [],
        array $resourcesRemoved = []
    ): array {
        $context = $this->repository->findClassContext($classId);

        if ($context === null) {
            throw new RuntimeException(
                'La clase indicada no existe.'
            );
        }

        if ($context['user_id'] !== $userId) {
            throw new AuthorizationException(
                'No tienes acceso a esta clase.'
            );
        }

        return $this->repository->transaction(
            function () use (
                $userId,
                $classId,
                $data,
                $resources,
                $resourcesRemoved,
                $context
            ) {
                $this->repository->updateClass(
                    classId: $classId,
                    data: [
                        'name' => $data['title'],
                        'slug' => Str::slug($data['title']),
                        'description' => $data['description'] ?? null,
                        'time' => $data['time'] ?? null,
                        'status' => self::STATUS_PENDING_REVIEW,
                    ]
                );

                if (!empty($resourcesRemoved)) {
                    $this->classResourceService->removeMany(
                        resources: $resourcesRemoved,
                        classId: $classId
                    );
                }

                if (!empty($resources)) {
                    $this->classResourceService->storeMany(
                        resources: $resources,
                        userId: $userId,
                        courseId: $context['course_id'],
                        classId: $classId
                    );
                }

                $this->repository->updateModuleStatus(
                    moduleId: $context['module_id'],
                    status: self::STATUS_PENDING_REVIEW
                );

                $this->repository->updateCourseStatus(
                    courseId: $context['course_id'],
                    status: self::STATUS_PENDING_REVIEW
                );

                return [
                    'status' => 'ok',
                ];
            }
        );
    }
}
