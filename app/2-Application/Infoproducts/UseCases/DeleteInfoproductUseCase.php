<?php

namespace Promolider\Application\Infoproducts\UseCases;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use App\Models\User;
use Exception;

class DeleteInfoproductUseCase
{
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(int $infoproductId, User $user): bool
    {
        $infoproduct = $this->infoproductRepository->findCourseById($infoproductId);

        if (!$infoproduct) {
            throw new Exception('Infoproducto no encontrado.');
        }

        if ($infoproduct->user_id !== $user->id) {
            throw new Exception('No tienes permisos para eliminar este curso.');
        }

        return $this->infoproductRepository->delete($infoproductId);
    }
}
