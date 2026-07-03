<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

class GetRegisteredDirectsUseCase
{
    private RegistrationRepositoryInterface $repository;

    public function __construct(RegistrationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $userId): array
    {
        $directs = $this->repository->getRegisteredDirects($userId);
        
        return [
            'rows' => $directs,
            'summary' => [
                'total_registro' => count($directs)
            ]
        ];
    }
}
