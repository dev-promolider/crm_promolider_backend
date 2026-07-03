<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;
use Exception;

class ValidatePreregistroTokenUseCase
{
    public function __construct(
        private PreregistroRepositoryInterface $preregistroRepository
    ) {}

    /**
     * Valida un token de preregistro y retorna los datos para hidratar el formulario.
     * 
     * Lógica extraída de: PreregistroController::retorno()
     */
    public function execute(string $token): array
    {
        $preregistro = $this->preregistroRepository->findByToken($token);

        if (!$preregistro) {
            throw new Exception('Token de preregistro no encontrado.', 404);
        }

        if (!$preregistro->isTokenValid()) {
            throw new Exception('El enlace de preregistro ha expirado. Solicita un nuevo enlace a tu patrocinador.', 410);
        }

        return [
            'preregistro_id'    => $preregistro->id,
            'nombres'           => $preregistro->name,
            'correo'            => $preregistro->email,
            'whatsapp'          => $preregistro->whatsapp,
            'referrer_username' => $preregistro->referrerUsername,
            'side'              => $preregistro->side,
            'account_type'      => $preregistro->accountType,
        ];
    }
}
