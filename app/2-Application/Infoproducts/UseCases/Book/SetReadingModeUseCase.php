<?php

namespace Promolider\Application\Infoproducts\UseCases\Book;

use App\Models\User;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

/**
 * El productor decide cómo se entrega su libro, igual que en Amazon el autor
 * decide si publica con o sin DRM:
 *
 *   online   -> el comprador solo puede leerlo dentro de la plataforma
 *   download -> además puede descargar los archivos
 */
class SetReadingModeUseCase
{
    public const MODES = ['online', 'download'];

    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(int $courseId, string $mode, User $user): string
    {
        if (!in_array($mode, self::MODES, true)) {
            throw new Exception('Modo de entrega no válido.');
        }

        $infoproduct = $this->infoproductRepository->findCourseById($courseId);

        if (!$infoproduct) {
            throw new InfoproductNotFoundException();
        }

        if ((int) $infoproduct->getProductTypeId() !== 2) {
            throw new Exception('El infoproducto no es un libro.');
        }

        if ($infoproduct->getUserId() !== $user->id && !$user->hasRole('Admin')) {
            throw new InfoproductNotOwnedException();
        }

        $this->infoproductRepository->update($courseId, ['reading_mode' => $mode]);

        return $mode;
    }
}
