<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface;

/**
 * Lo que cuesta el pre-registro, para poder enseñarlo antes de cobrarlo.
 *
 * La pantalla de pago llevaba el precio escrito a mano ($45.00, 18%, $53.10) mientras
 * el cobro salía de la base. Mientras coincidieran no se notaba; en cuanto el
 * administrador cambie el precio —y el plan nuevo lo cambia a $50— la pantalla diría
 * una cosa y la tarjeta cobraría otra.
 *
 * Sale del mismo sitio que el cobro, resolveAccountType(), para que no puedan
 * discrepar.
 */
class GetPreregistroPriceUseCase
{
    public function __construct(
        private RegistrationRepositoryInterface $registrationRepository
    ) {}

    /**
     * @return array{account_type_id: int, account: string, price: float, iva: float, total: float}
     */
    public function execute(): array
    {
        $accountType = $this->registrationRepository->resolveAccountType();

        $precio = (float) $accountType['price'];
        $iva = (float) $accountType['iva'];

        return [
            'account_type_id' => (int) $accountType['id'],
            'account'         => (string) ($accountType['account'] ?? 'Pre registro'),
            'price'           => round($precio, 2),
            'iva'             => round($iva, 2),
            // Exactamente el mismo cálculo que hace el cargo en Openpay.
            'total'           => round($precio + ($precio * ($iva / 100)), 2),
        ];
    }
}
