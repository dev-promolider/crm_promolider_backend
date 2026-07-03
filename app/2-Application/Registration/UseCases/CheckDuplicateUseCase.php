<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;

class CheckDuplicateUseCase
{
    public function __construct(
        private PreregistroRepositoryInterface $preregistroRepository
    ) {}

    /**
     * Verifica duplicados en campos únicos en tiempo real.
     * 
     * Lógica extraída de: PreregistroController::checkDuplicate()
     */
    public function execute(string $field, string $value): array
    {
        $allowedFields = ['email', 'username', 'nro_document', 'phone'];

        if (!in_array($field, $allowedFields)) {
            return [
                'field' => $field,
                'exists' => false,
                'message' => 'Campo no válido para verificación.',
            ];
        }

        $exists = $this->preregistroRepository->checkDuplicate($field, $value);

        $messages = [
            'email'        => 'Este correo ya está registrado.',
            'username'     => 'Este nombre de usuario ya está en uso.',
            'nro_document' => 'Este número de documento ya está registrado.',
            'phone'        => 'Este número de teléfono ya está registrado.',
        ];

        return [
            'field'   => $field,
            'value'   => $value,
            'exists'  => $exists,
            'message' => $exists ? $messages[$field] : null,
        ];
    }
}
