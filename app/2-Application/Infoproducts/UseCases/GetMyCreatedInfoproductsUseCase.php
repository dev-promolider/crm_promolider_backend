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

        // 2. Validar existencia de infoproductos
        if ($infoproducts['meta']['total'] === 0) {
            throw new Exception("No created infoproducts found for user ID: $userId", 404);
        }

        // 3. Devolver datos estructurados
        return $infoproducts;
    }
}
