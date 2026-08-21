<?php

namespace Promolider\Application\Infoproducts\UseCases\Book;

use App\Models\User;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Illuminate\Support\Facades\Log;
use Promolider\Domain\Infoproducts\Ports\Out\BookFileRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;
use Throwable;

class DeleteBookFileUseCase
{
    public function __construct(
        private BookFileRepositoryInterface $bookFileRepository,
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

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

        $this->deleteFromS3($bookFile['file_path']);

        return $this->bookFileRepository->delete($bookFileId);
    }

    /**
     * El archivo en S3 se borra antes que el registro, pero un fallo aquí no
     * debe impedir que el usuario quite el archivo de su libro.
     */
    private function deleteFromS3(?string $path): void
    {
        if (!$path || str_starts_with($path, 'http')) {
            return;
        }

        try {
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);

            $s3Client->deleteObject([
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => ltrim($path, '/'),
            ]);
        } catch (Throwable $th) {
            Log::warning('No se pudo eliminar el archivo del libro en S3', [
                'path' => $path,
                'error' => $th->getMessage(),
            ]);
        }
    }
}
