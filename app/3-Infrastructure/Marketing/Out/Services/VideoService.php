<?php

namespace Promolider\Infrastructure\Marketing\Out\Services;

use App\Models\Video;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\MultipartUploadException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VideoService
{
    private function createS3Client(): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'use_accelerate_endpoint' => true,
        ]);
    }

    /**
     * Genera una URL prefirmada para subir un video a S3.
     */
    public function generatePresignedUrl(): ?string
    {
        $s3Client = $this->createS3Client();
        $path = 'courses/class/';
        $filename = 'nombre_del_video.mp4';

        try {
            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $path . $filename,
                'ACL' => 'public-read',
            ]);

            return $s3Client->createPresignedRequest($cmd, '+15 minutes')->getUri()->__toString();
        } catch (S3Exception $e) {
            Log::error('Error generando URL firmada S3: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sube un video a S3 mediante MultipartUpload y guarda el registro en BD.
     */
    public function storeClassVideo($file, int $userId, int $courseId, int $classId): void
    {
        $name = $this->formatFilename($file->getClientOriginalName());
        $path = "courses/{$userId}/{$courseId}/{$classId}/class/";

        $s3Client = $this->createS3Client();

        $uploader = new MultipartUploader($s3Client, $file->getRealPath(), [
            'bucket' => env('AWS_BUCKET'),
            'key' => $path . $name,
            'ACL' => 'public-read',
        ]);

        try {
            $result = $uploader->upload();
            $url = $path . $name;

            Video::create([
                'filename' => $name,
                'path' => $url,
                'videoable_type' => 'test',
                'videoable_id' => 0,
                'class_id' => $classId,
                'saved_time' => 0,
            ]);

            Log::info('Video subido a S3', ['url' => $result['ObjectURL']]);
        } catch (MultipartUploadException $e) {
            Log::error('Error subiendo video a S3: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un video de S3 y su registro en BD.
     */
    public function deleteClassVideo($video): void
    {
        if ($video && $video->count() > 0) {
            Storage::disk('s3')->delete($video->first()->path);
            $video->first()->delete();
        }
    }

    /**
     * Actualiza un video en S3 (elimina el anterior, sube el nuevo).
     */
    public function updateClassVideo($file, int $userId, int $courseId, int $classId): void
    {
        $video = Video::where('class_id', $classId)->first();
        if (!$video) return;

        $s3Client = $this->createS3Client();

        // Eliminar el antiguo
        $s3Client->deleteObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $video->path,
        ]);

        // Subir el nuevo
        $name = $this->formatFilename($file->getClientOriginalName());
        $path = "courses/{$userId}/{$courseId}/{$classId}/class/";

        $uploader = new MultipartUploader($s3Client, $file->getRealPath(), [
            'bucket' => env('AWS_BUCKET'),
            'key' => $path . $name,
            'ACL' => 'public-read',
        ]);

        try {
            $result = $uploader->upload();
            $url = $path . $name;

            $video->update([
                'filename' => $name,
                'path' => $url,
                'videoable_type' => 'test',
                'videoable_id' => 0,
                'saved_time' => 0,
            ]);

            Log::info('Video actualizado en S3', ['url' => $result['ObjectURL']]);
        } catch (MultipartUploadException $e) {
            Log::error('Error actualizando video en S3: ' . $e->getMessage());
        }
    }

    /**
     * Formatea el nombre del archivo: limpia caracteres especiales.
     */
    private function formatFilename(string $original): string
    {
        $extension = pathinfo($original, PATHINFO_EXTENSION);
        $name = pathinfo($original, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');
        return $name . '.' . $extension;
    }
}
