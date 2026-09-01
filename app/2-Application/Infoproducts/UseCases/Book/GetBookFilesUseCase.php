<?php

namespace Promolider\Application\Infoproducts\UseCases\Book;

use App\Models\User;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Domain\Infoproducts\Ports\Out\BookFileRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

class GetBookFilesUseCase
{
    public function __construct(
        private BookFileRepositoryInterface $bookFileRepository,
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(int $courseId, User $user): array
    {
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

        $files = $this->bookFileRepository->findByCourseId($courseId);

        return [
            'files' => $files,
            'used_size' => $this->bookFileRepository->totalSizeByCourseId($courseId),
            'max_size' => StoreBookFileUseCase::MAX_TOTAL_SIZE,
            'max_files' => StoreBookFileUseCase::MAX_FILES,
            // Se devuelven aquí para que la vista no necesite una segunda
            // petición a /course/{id}, que sí exige ser el dueño del recurso.
            'course' => [
                'id' => $infoproduct->getId(),
                'title' => $infoproduct->getTitle(),
                'status' => $infoproduct->getStatus(),
                'reading_mode' => $infoproduct->readingMode(),
            ],
        ];
    }
}
