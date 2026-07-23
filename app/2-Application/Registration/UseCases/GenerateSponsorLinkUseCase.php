<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;
use DateTime;
use DateInterval;

class GenerateSponsorLinkUseCase
{
    private RegistrationRepositoryInterface $repository;

    public function __construct(RegistrationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $userId): array
    {
        // 1. Eliminar enlaces expirados
        $this->repository->deleteExpiredSponsorLinks($userId);

        // 2. Revisar si hay un enlace activo
        $existingLink = $this->repository->getActiveSponsorLink($userId);
        if ($existingLink) {
            return [
                'success' => false,
                'message' => 'Ya tienes un enlace activo',
                'resource' => $existingLink
            ];
        }

        // 3. Crear nuevo enlace por 5 horas
        $now = new DateTime('now', new \DateTimeZone('UTC'));
        $end = clone $now;
        $end->add(new DateInterval('PT5H'));

        $url = $this->generateUniqueUrl($userId);

        $link = $this->repository->createSponsorLink($userId, $url, $now, $end);

        return [
            'success' => true,
            'message' => 'Enlace generado',
            'resource' => $link
        ];
    }

    private function generateUniqueUrl(int $userId): string
    {
        $baseUrl = rtrim(config('app.frontend_url'), '/');
        $timestamp = time();
        $randomString = substr(md5($userId . $timestamp . uniqid()), 0, 8);
        return "{$baseUrl}/register/{$userId}/{$timestamp}/{$randomString}";
    }
}
