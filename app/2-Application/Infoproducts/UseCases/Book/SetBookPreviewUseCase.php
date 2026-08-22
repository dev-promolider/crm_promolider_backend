<?php

namespace Promolider\Application\Infoproducts\UseCases\Book;

use App\Models\User;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Domain\Infoproducts\Ports\Out\BookFileRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

class SetBookPreviewUseCase
{
    /**
     * Solo se admite PDF: es el único formato que los navegadores muestran
     * incrustado sin necesidad de descargarlo.
     */
    public const PREVIEWABLE_FORMATS = ['pdf'];

    public function __construct(
        private BookFileRepositoryInterface $bookFileRepository,
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    /**
     * Marca el archivo como muestra gratuita. Si ya lo era, se desmarca, de
     * modo que el mismo botón sirve para poner y quitar la muestra.
     */
    public function execute(int $bookFileId, User $user): bool
    {
        $bookFile = $this->bookFileRepository->findById($bookFileId);

        if (!$bookFile) {
            throw new Exception('El archivo no existe.');
        }

        $infoproduct = $this->infoproductRepository->findCourseById($bookFile['course_id']);

        if (!$infoproduct) {
            throw new InfoproductNotFoundException();
        }

        if ($infoproduct->getUserId() !== $user->id && !$user->hasRole('Admin')) {
            throw new InfoproductNotOwnedException();
        }

        if (!in_array($bookFile['file_type'], self::PREVIEWABLE_FORMATS, true)) {
            throw new Exception('Solo un archivo PDF puede usarse como vista previa.');
        }

        $activar = !$bookFile['is_preview'];

        $this->bookFileRepository->setPreview(
            $bookFile['course_id'],
            $activar ? $bookFileId : null
        );

        return $activar;
    }
}
