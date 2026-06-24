<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Aws\S3\Exception\S3Exception;
use Aws\Exception\MultipartUploadException;
use App\Helpers\Helper;

trait S3FileTrait
{
    private $s3Client;

    /**
     * Inicializar cliente S3
     */
    protected function initializeS3Client()
    {
        $this->s3Client = new S3Client([
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
     * Subir video a S3 usando MultipartUploader
     */
    protected function uploadVideoToS3($videoFile, $path)
    {
        try {
            $filename = Helper::formatFilenameSecond($videoFile->getClientOriginalName());
            $fullPath = $path . $filename;

            $uploader = new MultipartUploader($this->s3Client, $videoFile->getRealPath(), [
                'bucket' => env('AWS_BUCKET'),
                'key' => $fullPath,
                'ACL' => 'public-read',
            ]);

            $result = $uploader->upload();
            
            Log::info('Video subido a S3', ['path' => $fullPath, 'url' => $result['ObjectURL']]);
            
            return [
                'success' => true,
                'path' => $fullPath,
                'filename' => $filename,
                'url' => $result['ObjectURL']
            ];
        } catch (MultipartUploadException $e) {
            Log::error('Error subiendo video a S3', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Subir archivo genérico a S3
     */
    protected function uploadFileToS3($file, $path)
    {
        try {
            $filename = Helper::formatFilenameSecond($file->getClientOriginalName());
            $fullPath = $path . $filename;

            $result = Storage::disk('s3')->put($fullPath, file_get_contents($file->getRealPath()), 'public');
            
            if ($result) {
                Log::info('Archivo subido a S3', ['path' => $fullPath]);
                return [
                    'success' => true,
                    'path' => $fullPath,
                    'filename' => $filename
                ];
            }
            
            return ['success' => false, 'error' => 'No se pudo subir el archivo'];
        } catch (\Exception $e) {
            Log::error('Error subiendo archivo a S3', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar archivo de S3
     */
    protected function deleteFileFromS3($path)
    {
        try {
            // Extraer la ruta relativa si es una URL completa
            if (str_contains($path, env('STORAGE_DOMAIN'))) {
                $path = str_replace(env('STORAGE_DOMAIN') . '/', '', $path);
            }
            
            Storage::disk('s3')->delete($path);
            Log::info('Archivo eliminado de S3', ['path' => $path]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error eliminando archivo de S3', ['path' => $path, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generar URL de S3
     */
    protected function generateS3Url($path)
    {
        // Si ya es una URL completa, devolverla tal como está
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Si comienza con el dominio de storage, devolverla tal como está
        if (str_starts_with($path, 'https://')) {
            return $path;
        }

        // Generar URL desde el path relativo
        return env('STORAGE_DOMAIN') . '/' . ltrim($path, '/');
    }

    /**
     * Obtener información del archivo desde S3
     */
    protected function getS3FileInfo($path)
    {
        try {
            // Extraer la ruta relativa si es una URL completa
            if (str_contains($path, env('STORAGE_DOMAIN'))) {
                $path = str_replace(env('STORAGE_DOMAIN') . '/', '', $path);
            }

            if (Storage::disk('s3')->exists($path)) {
                return [
                    'exists' => true,
                    'size' => Storage::disk('s3')->size($path),
                    'last_modified' => Storage::disk('s3')->lastModified($path),
                    'url' => Storage::disk('s3')->url($path)
                ];
            }

            return ['exists' => false];
        } catch (\Exception $e) {
            Log::error('Error obteniendo información del archivo S3', [
                'path' => $path, 
                'error' => $e->getMessage()
            ]);
            return ['exists' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Copiar archivo dentro de S3
     */
    protected function copyS3File($sourcePath, $destinationPath)
    {
        try {
            // Extraer rutas relativas si son URLs completas
            if (str_contains($sourcePath, env('STORAGE_DOMAIN'))) {
                $sourcePath = str_replace(env('STORAGE_DOMAIN') . '/', '', $sourcePath);
            }

            Storage::disk('s3')->copy($sourcePath, $destinationPath);
            
            Log::info('Archivo copiado en S3', [
                'source' => $sourcePath,
                'destination' => $destinationPath
            ]);

            return [
                'success' => true,
                'source_path' => $sourcePath,
                'destination_path' => $destinationPath,
                'destination_url' => Storage::disk('s3')->url($destinationPath)
            ];
        } catch (\Exception $e) {
            Log::error('Error copiando archivo en S3', [
                'source' => $sourcePath,
                'destination' => $destinationPath,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Mover archivo dentro de S3
     */
    protected function moveS3File($sourcePath, $destinationPath)
    {
        try {
            // Extraer rutas relativas si son URLs completas
            if (str_contains($sourcePath, env('STORAGE_DOMAIN'))) {
                $sourcePath = str_replace(env('STORAGE_DOMAIN') . '/', '', $sourcePath);
            }

            Storage::disk('s3')->move($sourcePath, $destinationPath);
            
            Log::info('Archivo movido en S3', [
                'source' => $sourcePath,
                'destination' => $destinationPath
            ]);

            return [
                'success' => true,
                'source_path' => $sourcePath,
                'destination_path' => $destinationPath,
                'destination_url' => Storage::disk('s3')->url($destinationPath)
            ];
        } catch (\Exception $e) {
            Log::error('Error moviendo archivo en S3', [
                'source' => $sourcePath,
                'destination' => $destinationPath,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Limpiar directorio de S3 (eliminar archivos antiguos)
     */
    protected function cleanS3Directory($directoryPath, $olderThanDays = 30)
    {
        try {
            $files = Storage::disk('s3')->allFiles($directoryPath);
            $deletedCount = 0;
            $cutoffDate = now()->subDays($olderThanDays)->timestamp;

            foreach ($files as $file) {
                $lastModified = Storage::disk('s3')->lastModified($file);
                
                if ($lastModified < $cutoffDate) {
                    Storage::disk('s3')->delete($file);
                    $deletedCount++;
                    Log::info('Archivo antiguo eliminado de S3', [
                        'file' => $file,
                        'last_modified' => date('Y-m-d H:i:s', $lastModified)
                    ]);
                }
            }

            Log::info('Limpieza de directorio S3 completada', [
                'directory' => $directoryPath,
                'deleted_files' => $deletedCount,
                'older_than_days' => $olderThanDays
            ]);

            return [
                'success' => true,
                'deleted_files' => $deletedCount,
                'directory' => $directoryPath
            ];

        } catch (\Exception $e) {
            Log::error('Error limpiando directorio S3', [
                'directory' => $directoryPath,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}