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
}
