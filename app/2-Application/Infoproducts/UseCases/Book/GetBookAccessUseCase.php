<?php

namespace Promolider\Application\Infoproducts\UseCases\Book;

use App\Models\PurchasedCourse;
use App\Models\User;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Domain\Infoproducts\Ports\Out\BookFileRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Exception;

/**
 * Entrega el contenido del libro a quien lo compró.
 *
 * Las URLs que devuelve están firmadas y caducan, y su disposición depende del
 * modo de entrega que eligió el productor: 'inline' para leer dentro de la
 * plataforma, 'attachment' cuando además permite descargar.
 */
class GetBookAccessUseCase
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

        $esDueno = $infoproduct->getUserId() === $user->id || $user->hasRole('Admin');

        $loCompro = PurchasedCourse::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();

        if (!$esDueno && !$loCompro) {
            throw new Exception('Necesitas adquirir este libro para acceder a su contenido.', 403);
        }

        $puedeDescargar = $infoproduct->allowsDownload();

        // La muestra gratuita no se incluye: no forma parte de lo que se compra.
        $archivos = array_values(array_filter(
            $this->bookFileRepository->findByCourseId($courseId),
            fn (array $file) => !$file['is_preview']
        ));

        $archivos = array_map(function (array $file) use ($puedeDescargar) {
            return [
                'id' => $file['id'],
                'file_name' => $file['file_name'],
                'file_type' => $file['file_type'],
                'size' => $file['size'],
                'url' => $this->bookFileRepository->temporaryUrl(
                    $file['file_path'],
                    $file['file_name'],
                    $puedeDescargar
                ),
            ];
        }, $archivos);

        return [
            'book' => [
                'id' => $infoproduct->getId(),
                'title' => $infoproduct->getTitle(),
                'description' => $infoproduct->getDescription(),
                'url_portada' => $infoproduct->getUrlPortada(),
                'reading_mode' => $infoproduct->readingMode(),
            ],
            'can_download' => $puedeDescargar,
            'files' => $archivos,
        ];
    }
}
