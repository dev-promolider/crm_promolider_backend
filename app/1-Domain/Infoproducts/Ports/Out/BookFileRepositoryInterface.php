<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

interface BookFileRepositoryInterface
{
    /**
     * Archivos de un libro, con su URL pública ya resuelta.
     */
    public function findByCourseId(int $courseId): array;

    /**
     * Suma en bytes de todos los archivos ya almacenados para el libro.
     */
    public function totalSizeByCourseId(int $courseId): int;

    public function countByCourseId(int $courseId): int;

    public function create(array $data): array;

    public function findById(int $bookFileId): ?array;

    public function delete(int $bookFileId): bool;

    /**
     * Archivo ofrecido como muestra gratuita del libro, si lo hay.
     */
    public function findPreviewByCourseId(int $courseId): ?array;

    /**
     * Marca un archivo como muestra gratuita y desmarca cualquier otro del
     * mismo libro. Con $bookFileId nulo, el libro se queda sin muestra.
     */
    public function setPreview(int $courseId, ?int $bookFileId): void;

    /**
     * URL de acceso temporal a un archivo del libro.
     *
     * @param bool $descargable true entrega el archivo como descarga; false lo
     *                          fuerza a abrirse dentro del navegador.
     */
    public function temporaryUrl(string $path, string $fileName, bool $descargable): ?string;
}
