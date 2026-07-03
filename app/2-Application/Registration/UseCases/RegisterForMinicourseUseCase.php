<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

class RegisterForMinicourseUseCase
{
    public function __construct(
        private RegistrationRepositoryInterface $repository
    ) {}

    public function execute(int $userId): void
    {
        $this->repository->registerMinicourseParticipant($userId);
    }
}
