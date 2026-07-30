<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Illuminate\Auth\Access\AuthorizationException;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use RuntimeException;

final class GetModuleClassDetailsUseCase
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

        if ($context['user_id'] !== $userId) {
            throw new AuthorizationException(
                'No tienes acceso a esta clase.'
            );
        }

        $details = $this->repository->findClassDetails($classId);

        if ($details === null) {
            throw new RuntimeException(
                'No se encontraron los detalles de la clase.'
            );
        }

        return $details;
    }
}
