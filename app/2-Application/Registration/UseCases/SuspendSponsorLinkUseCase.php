<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

class SuspendSponsorLinkUseCase
{
    private RegistrationRepositoryInterface $repository;

    public function __construct(RegistrationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $linkId, int $userId): array
    {
        $success = $this->repository->suspendSponsorLink($linkId, $userId);

        if ($success) {
            return [
                'success' => true,
                'message' => 'Enlace suspendido exitosamente',
                'state' => 200
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al suspender el enlace o enlace no encontrado',
            'state' => 400
        ];
    }
}
