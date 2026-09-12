<?php

namespace Promolider\Application\Wallet\UseCases\BinaryCut;

use Promolider\Domain\Wallet\Ports\Out\WalletRepositoryInterface;

class ExecuteBinaryCutUseCase
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository
    ) {}

    /**
     * @param  bool      $forzar        Repetir un periodo ya cortado. Solo administrador.
     * @param  int|null  $ejecutadoPor  Quien lo lanza; nulo si viene del programador.
     *
     * @return array{lote: int, periodo: string, pagados: int, total_binario: float, total_generacional: float}
     */
    public function execute(bool $forzar = false, ?int $ejecutadoPor = null): array
    {
        return $this->walletRepository->executeBinaryCut($forzar, $ejecutadoPor);
    }
}
