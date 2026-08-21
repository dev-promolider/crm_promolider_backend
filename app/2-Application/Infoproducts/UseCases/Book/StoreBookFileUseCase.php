<?php

namespace Promolider\Application\Infoproducts\UseCases\Book;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Domain\Infoproducts\Ports\Out\BookFileRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

class StoreBookFileUseCase
{
    public const MAX_TOTAL_SIZE = 500 * 1024 * 1024; // 500 MB
    public const MAX_FILES = 20;

    public const ALLOWED_FORMATS = ['pdf', 'epub', 'xls', 'xlsx', 'xlsm', 'xlsb', 'csv'];

    public function __construct(
        private BookFileRepositoryInterface $bookFileRepository,
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(int $courseId, UploadedFile $file, User $user): array
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

        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, self::ALLOWED_FORMATS, true)) {
            throw new Exception('El formato ' . strtoupper($extension) . ' no está permitido.');
        }

        if ($this->bookFileRepository->countByCourseId($courseId) >= self::MAX_FILES) {
            throw new Exception('Este libro ya alcanzó el máximo de ' . self::MAX_FILES . ' archivos.');
        }

        $usedSize = $this->bookFileRepository->totalSizeByCourseId($courseId);

        if ($usedSize + $file->getSize() > self::MAX_TOTAL_SIZE) {
            throw new Exception('El tamaño total de los archivos excede el límite permitido de 500 MB.');
        }

        // Se conserva la estructura de carpetas del sistema anterior para que
        // los archivos ya subidos sigan siendo válidos.
        $ownerId = $infoproduct->getUserId();
        $filename = $file->getClientOriginalName();
        $path = 'books/' . $ownerId . '/' . $courseId . '/' . Str::uuid()->toString();

        $this->uploadToS3($file, $path . '/' . $filename);

        return $this->bookFileRepository->create([
            'course_id' => $courseId,
            'file_type' => $extension,
            'file_name' => $filename,
            'file_path' => $path . '/' . $filename,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    private function uploadToS3(UploadedFile $file, string $key): void
    {
        $s3Client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $uploader = new \Aws\S3\MultipartUploader($s3Client, $file->getRealPath(), [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'key' => $key,
            'ACL' => 'public-read',
        ]);

        $uploader->upload();
    }
}
