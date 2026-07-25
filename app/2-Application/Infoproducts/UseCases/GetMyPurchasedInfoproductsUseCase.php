<?php

namespace Promolider\Application\Infoproducts\UseCases;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

class GetMyPurchasedInfoproductsUseCase
{
    // Inyección de dependencias del repositorio de infoproductos
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(string $userId): array
    {
        // 1. Obtener infoproductos comprados por el usuario
        $infoproducts = $this->infoproductRepository->findPurchasedByUserId($userId);

        // 2. Asegurar que sea un arreglo vacío si no hay resultados en lugar de fallar
        if (!$infoproducts) {
            $infoproducts = [];
        }

        // 3. Devolver datos estructurados
        return [
            'user_id' => $userId,
            'purchased_infoproducts' => $infoproducts,
        ];
    }
}
