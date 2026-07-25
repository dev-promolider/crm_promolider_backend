<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

interface ClassVideoStorageInterface
{
    /**
     * @return array{
     *     filename: string,
     *     path: string,
     *     upload_url: string
     * }
     */
    public function createPresignedUploadUrl(
        int $userId,
        int $courseId,
        int $classId,
        string $filename
    ): array;
}
