<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

class CheckUserAvailabilityUseCase
{
    public function __construct(
        private RegistrationRepositoryInterface $repository
    ) {}

    public function execute(string $field, string $value, ?int $documentType = null): bool
    {
        return $this->repository->checkAvailability($field, $value, $documentType);
    }
}
