<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

class RegisterForEbookUseCase
{
    public function __construct(
        private RegistrationRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $ebookId = null): void
    {
        $this->repository->registerEbookParticipant($userId, $ebookId);
    }
}
