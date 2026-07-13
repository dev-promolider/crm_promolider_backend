<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class GetParticipantsFeedUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica) {
            throw new \RuntimeException('Dinámica no encontrada', 404);
        }

        return $this->dinamicaRepository->getParticipantsFeed($dinamica->id);
    }
}
