<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Infrastructure\Marketing\Out\Services\VideoService;

class VideoController extends Controller
{
    public function __construct(
        private VideoService $videoService
    ) {}

    /**
     * GET /marketing/courses/video/stream?class_id={classId}
     * Retorna la URL del video S3 para una clase.
     */
    public function streamVideo(Request $request): JsonResponse
    {
        try {
            $classId = $request->input('class_id');
            if (!$classId) {
                return response()->json(['message' => 'class_id es requerido'], 400);
            }

            $video = Video::where('class_id', $classId)
                ->select('path')
                ->first();

            if (!$video) {
                return response()->json(['message' => 'Video no encontrado para esta clase'], 404);
            }

            $path = \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl(
                $video->path,
                now()->addHours(2)
            );

            return response()->json([
                'data' => $path,
                'message' => 'Url recuperada con exito',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener stream de video: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener video'], 500);
        }
    }

    /**
     * POST /marketing/courses/video/save-time
     * Body: { id: video_id, time: float }
     * Guarda el tiempo de reproduccion del video.
     */
    public function saveTime(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:videos,id',
                'time' => 'required|numeric|min:0',
            ]);

            $video = Video::findOrFail($validated['id']);
            $video->saved_time = $validated['time'];
            $video->save();

            return response()->json([
                'message' => 'video time saved',
                'data' => '',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al guardar tiempo de video: ' . $e->getMessage());
            return response()->json(['message' => 'Error al guardar tiempo'], 500);
        }
    }

    /**
     * GET /marketing/courses/video/show-time?id={videoId}
     * Retorna el tiempo guardado de un video.
     */
    public function showTime(Request $request): JsonResponse
    {
        try {
            $videoId = $request->input('id');
            if (!$videoId) {
                return response()->json(['message' => 'id es requerido'], 400);
            }

            $video = Video::findOrFail($videoId);

            return response()->json([
                'message' => '',
                'data' => $video->saved_time,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tiempo de video: ' . $e->getMessage());
            return response()->json(['message' => 'Error al obtener tiempo'], 500);
        }
    }

    /**
     * PATCH /marketing/courses/video/update-status
     * Body: { id: video_id }
     * Actualiza el status del video a 1.
     */
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:videos,id',
            ]);

            $video = Video::findOrFail($validated['id']);
            $video->status = 1;
            $video->save();

            return response()->json(['message' => 'Status actualizado']);
        } catch (\Exception $e) {
            Log::error('Error al actualizar status de video: ' . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar status'], 500);
        }
    }

    /**
     * GET /marketing/courses/video/generate-url
     * Genera una URL prefirmada de S3 para subir un video.
     */
    public function generatePresignedUrl(): JsonResponse
    {
        try {
            $url = $this->videoService->generatePresignedUrl();

            if (!$url) {
                return response()->json(['message' => 'Error al generar URL'], 500);
            }

            return response()->json([
                'data' => $url,
                'message' => 'Url generada',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al generar URL presigned: ' . $e->getMessage());
            return response()->json(['message' => 'Error al generar URL'], 500);
        }
    }

    /**
     * GET /marketing/courses/video/update-video-url/{id}/{name}
     * Reemplaza el video de una clase y genera una URL prefirmada.
     */
    public function updateVideo(int $id, string $filename): JsonResponse
    {
        try {
            $user = request()->user();
            if (!$user) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            // Obtener datos del curso/modulo/clase
            $clas = \App\Models\Clas::find($id);
            if (!$clas) {
                return response()->json(['message' => 'Clase no encontrada'], 404);
            }

            $module = \App\Models\Module::find($clas->id_modules);
            $course = $module ? \App\Models\Course::find($module->id_courses) : null;

            $courseId = $course ? $course->id : 0;
            $path = "courses/{$user->id}/{$courseId}/{$id}/class/";
            $url = $path . $filename;

            // Config S3
            $s3Client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => env('AWS_DEFAULT_REGION'),
                'credentials' => [
                    'key' => env('AWS_ACCESS_KEY_ID'),
                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                ],
                'use_accelerate_endpoint' => true,
            ]);

            try {
                $cmd = $s3Client->getCommand('PutObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $url,
                    'ACL' => 'public-read',
                ]);

                // Eliminar video anterior si existe
                $existingVideo = Video::where('class_id', $id)->first();
                if ($existingVideo) {
                    $s3Client->deleteObject([
                        'Bucket' => env('AWS_BUCKET'),
                        'Key' => $existingVideo->path,
                    ]);

                    $existingVideo->update([
                        'filename' => $filename,
                        'path' => $url,
                        'videoable_type' => 'test',
                        'videoable_id' => 0,
                        'saved_time' => 0,
                    ]);
                } else {
                    Video::create([
                        'filename' => $filename,
                        'path' => $url,
                        'videoable_type' => 'test',
                        'videoable_id' => 0,
                        'class_id' => $id,
                        'saved_time' => 0,
                    ]);
                }

                $presignedUrl = $s3Client->createPresignedRequest($cmd, '+15 minutes')->getUri()->__toString();

                return response()->json([
                    'data' => $presignedUrl,
                    'message' => 'Url generada',
                ]);
            } catch (\Aws\S3\Exception\S3Exception $e) {
                Log::error('Error S3 al actualizar video: ' . $e->getMessage());
                return response()->json(['message' => 'Error al procesar video en S3'], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar video: ' . $e->getMessage());
            return response()->json(['message' => 'Error al actualizar video'], 500);
        }
    }
}
