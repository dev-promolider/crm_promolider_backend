<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Storage;

use Aws\S3\S3Client;
use Illuminate\Support\Str;
use Promolider\Domain\Infoproducts\Ports\Out\ClassVideoStorageInterface;
use RuntimeException;

final class S3ClassVideoStorageService implements ClassVideoStorageInterface
{
    public function createPresignedUploadUrl(
        int $userId,
        int $courseId,
        int $classId,
        string $filename
    ): array {
        $filename = $this->formatFilename($filename);

        $path = sprintf(
            'courses/%d/%d/%d/class/%s',
            $userId,
            $courseId,
            $classId,
            $filename
        );

        $client = $this->createS3Client();

        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
            'ACL' => 'public-read',
        ]);

        $request = $client->createPresignedRequest(
            $command,
            '+15 minutes'
        );

        return [
            'filename' => $filename,
            'path' => $path,
            'upload_url' => (string) $request->getUri(),
        ];
    }

    private function createS3Client(): S3Client
    {
        $bucket = config('filesystems.disks.s3.bucket');

        if (empty($bucket)) {
            throw new RuntimeException(
                'No se encuentra configurado el bucket de S3.'
            );
        }

        return new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
            'use_accelerate_endpoint' => true,
        ]);
    }

    private function formatFilename(string $filename): string
    {
        $filename = basename($filename);

        $extension = strtolower(
            pathinfo($filename, PATHINFO_EXTENSION)
        );

        $name = pathinfo($filename, PATHINFO_FILENAME);

        $name = Str::slug($name);

        if ($name === '') {
            $name = 'video';
        }

        return $extension !== ''
            ? "{$name}.{$extension}"
            : $name;
    }
}
