<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;
use Exception;

class GetPreregistroConfigUseCase
{
    public function __construct(
        private PreregistroRepositoryInterface $preregistroRepository
    ) {}

    /**
     * Obtiene la configuración del enlace de preregistro (lado binario, tema, datos del referidor).
     * 
     * Lógica extraída de: PreregistroController::index() + getConfig()
     */
    public function execute(string $username): array
    {
        // Delegamos al repositorio que busca PreregistroLink + User
        // Esta lógica la implementa el adaptador de infraestructura
        return $this->preregistroRepository->getPreregistroConfig($username);
    }
}
