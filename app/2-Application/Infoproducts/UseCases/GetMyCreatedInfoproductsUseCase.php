<?php

namespace Promolider\Application\Infoproducts\UseCases;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

class GetMyCreatedInfoproductsUseCase
{
    // Inyección de dependencias del repositorio de infoproductos
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(
        int $userId,
        int $page = 1,
        int $perPage = 10,
        ?string $search = null,
        ?int $productTypeId = null
    ): array
    {
        // 1. Obtener infoproductos creados por el usuario
        $infoproducts = $this->infoproductRepository->findCreatedByUserIdPaginated(
            $userId,
            $page,
            $perPage,
            $search,
            $productTypeId
        );

        // 2. (Validación eliminada: se devuelve la estructura vacía correctamente en lugar de lanzar una excepción)

        // 3. Devolver datos estructurados
        return $infoproducts;
    }
}
