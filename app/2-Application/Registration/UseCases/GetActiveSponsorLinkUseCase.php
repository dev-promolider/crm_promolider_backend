<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

class GetActiveSponsorLinkUseCase
{
    private RegistrationRepositoryInterface $repository;

    public function __construct(RegistrationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $userId): array
    {
        $this->repository->deleteExpiredSponsorLinks($userId);
        
        $link = $this->repository->getActiveSponsorLink($userId);
        
        if (!$link) {
            return [
                'tiempoRestanteEnSegundos' => 0,
                'fechaFin' => null,
                'link' => null
            ];
        }

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $fechaFin = new \DateTime($link['fecha_fin'], new \DateTimeZone('UTC'));
        
        $tiempoRestanteEnSegundos = $fechaFin->getTimestamp() - $now->getTimestamp();

        if ($tiempoRestanteEnSegundos <= 0) {
            $this->repository->suspendSponsorLink($link['id'], $userId);
            return [
                'tiempoRestanteEnSegundos' => 0,
                'fechaFin' => null,
                'link' => null
            ];
        }

        return [
            'tiempoRestanteEnSegundos' => $tiempoRestanteEnSegundos,
            'fechaFin' => $link['fecha_fin'],
            'link' => $link
        ];
    }
}
