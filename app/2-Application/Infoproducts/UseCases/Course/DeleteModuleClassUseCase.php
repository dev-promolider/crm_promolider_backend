<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Illuminate\Auth\Access\AuthorizationException;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use RuntimeException;

final class DeleteModuleClassUseCase
{
    public function __construct(
        private ModuleClassRepositoryInterface $repository
    ) {
    }

    public function execute(
        int $userId,
        int $classId
    ): array {
        $context = $this->repository->findClassContext($classId);

        if ($context === null) {
            throw new RuntimeException(
                'La clase indicada no existe.'
            );
        }

        if ((int) $context['user_id'] !== $userId) {
            throw new AuthorizationException(
                'No tienes autorización para eliminar esta clase.'
            );
        }

        $this->repository->deleteClassWithRelations($classId);

        return [
            'status' => 'ok',
        ];
    }
}
