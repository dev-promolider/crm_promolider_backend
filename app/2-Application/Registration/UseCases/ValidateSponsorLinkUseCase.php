<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

class ValidateSponsorLinkUseCase
{
    public function __construct(
        private RegistrationRepositoryInterface $repository
    ) {}

    public function execute(int $userId, string $code): ?array
    {
        return $this->repository->validateSponsorLink($userId, $code);
    }
}
