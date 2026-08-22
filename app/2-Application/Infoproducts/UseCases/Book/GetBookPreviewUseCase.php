<?php

namespace Promolider\Application\Infoproducts\UseCases\Book;

use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Domain\Infoproducts\Ports\Out\BookFileRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

/**
 * Devuelve la muestra gratuita de un libro para la ficha de venta.
 *
 * Es el único punto por el que se expone públicamente un archivo del libro, y
 * solo el que el productor haya marcado como muestra: el resto sigue siendo
 * accesible únicamente para el dueño.
 */
class GetBookPreviewUseCase
{
    public function __construct(
        private BookFileRepositoryInterface $bookFileRepository,
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(int $courseId): ?array
    {
        $infoproduct = $this->infoproductRepository->findCourseById($courseId);

        if (!$infoproduct) {
            throw new InfoproductNotFoundException();
        }

        if ((int) $infoproduct->getProductTypeId() !== 2) {
            throw new Exception('El infoproducto no es un libro.');
        }

        $preview = $this->bookFileRepository->findPreviewByCourseId($courseId);

        if (!$preview) {
            return null;
        }

        return [
            'file_name' => $preview['file_name'],
            'file_type' => $preview['file_type'],
            'size' => $preview['size'],
            'url' => $preview['url'],
        ];
    }
}
