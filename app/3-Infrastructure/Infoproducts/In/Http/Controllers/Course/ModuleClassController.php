<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\Lesson\GetModuleClassDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\Lesson\CreateClassUseCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Aws\S3\S3Client;
use App\Models\Clas;
use App\Models\Video;
use App\Helpers\Helper;

class ModuleClassController extends Controller
{
    public function __construct(
        private GetModuleClassDataUseCase $getModuleClassDataUseCase,
        private CreateClassUseCase $createClassUseCase
    ) {}

    public function getClassList(Request $request)
    {
        $moduleId = (int) $request->route('moduleId');

        $lessonData = $this->getModuleClassDataUseCase->execute($moduleId);

        return response()->json($lessonData, 200);
    }

    /**
     * GET class/show-class/{courseId}?name=...
     * Devuelve la clase activa para el reproductor del VCR.
     * Si se pasa ?name=slug, busca esa clase; si no, retorna la primera del curso.
     */
    public function showClass(Request $request, int $courseId)
    {
        try {
            $name = $request->query('name');

            // Buscar la clase por slug/name dentro del curso
            $class = null;
            if (!empty($name)) {
                $class = Clas::whereHas('module', function ($q) use ($courseId) {
                        $q->where('id_courses', $courseId);
                    })
                    ->where(function ($q) use ($name) {
                        $q->where('slug', $name)
                          ->orWhere('name', $name);
                    })
                    ->with('video')
                    ->first();
            }

            // Si no se encontró por nombre, tomar la primera (orden ascendente)
            if (!$class) {
                $class = Clas::whereHas('module', function ($q) use ($courseId) {
                        $q->where('id_courses', $courseId);
                    })
                    ->orderBy('order', 'asc')
                    ->with('video')
                    ->first();
            }

            if (!$class) {
                return response()->json(['message' => 'No se encontró ninguna clase para este curso.'], 404);
            }

            $data = $class->toArray();

            // Incluir URL del video si existe
            if ($class->video) {
                $data['video_path'] = $class->video->path ?? null;
                $data['video_url']  = $class->video->path ? 
                    'https://' . config('filesystems.disks.s3.bucket') . '.s3.amazonaws.com/' . $class->video->path 
                    : null;
            }

            return response()->json($data, 200);

        } catch (\Throwable $th) {
            Log::error('showClass error: ' . $th->getMessage());
            return response()->json(['message' => 'Error al obtener la clase'], 500);
        }
    }

    /**
     * GET course/details/{courseId}
     * Devuelve los detalles bA!sicos del curso para el estudiante en el VCR.
     */
    public function getCourseDetails(Request $request, int $courseId)
    {
        try {
            $course = \App\Models\Course::with('instructor')->find($courseId);
            if (!$course) {
                return response()->json(['message' => 'Curso no encontrado'], 404);
            }
            return response()->json(['data' => $course], 200);
        } catch (\Throwable $th) {
            Log::error('getCourseDetails error: ' . $th->getMessage());
            return response()->json(['message' => 'Error al obtener los detalles del curso'], 500);
        }
    }

    /**
     * GET course/temary/get-all-class/{courseId}
     * Devuelve el temario completo del curso para el estudiante en el VCR.
     */
    public function getCourseTemary(Request $request, int $courseId)
    {
        try {
            $course = \App\Models\Course::with(['modules.classes' => function ($query) {
                $query->orderBy('order', 'asc');
            }])->find($courseId);

            if (!$course) {
                return response()->json(['message' => 'Curso no encontrado'], 404);
            }

            // Mapear "classes" a "lessons" porque asA- lo espera el frontend (VCR)
            $courseData = $course->toArray();
            foreach ($courseData['modules'] as &$module) {
                $module['lessons'] = $module['classes'];
                unset($module['classes']);
            }

            return response()->json(['data' => $courseData], 200);
        } catch (\Throwable $th) {
            Log::error('getCourseTemary error: ' . $th->getMessage());
            return response()->json(['message' => 'Error al obtener el temario del curso'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description
            ];
            
            $files = $request->hasFile('resources') ? $request->file('resources') : null;
            $user = $request->user();
            $moduleId = $request->module_id;

            $result = $this->createClassUseCase->execute($moduleId, $data, $user, $files);

            return response()->json([
                'data' => $result,
                'message' => 'Clase creada exitosamente'
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error creating class: ' . $th->getMessage());
            return response()->json([
                'data' => ['status' => 'error'],
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, $id, \Promolider\Application\Infoproducts\UseCases\Course\Lesson\UpdateClassUseCase $updateClassUseCase)
    {
        try {
            $data = $request->only(['title', 'description', 'module_id']);
            $files = $request->hasFile('resources') ? $request->file('resources') : null;
            $user = $request->user();

            $result = $updateClassUseCase->execute((int)$id, $data, $user, $files);
            return response()->json([
                'data' => $result,
                'message' => 'Clase actualizada exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error updating class: ' . $th->getMessage());
            return response()->json(['message' => 'Error al actualizar la clase'], 422);
        }
    }

    public function destroy($id, \Promolider\Application\Infoproducts\UseCases\Course\Lesson\DeleteClassUseCase $deleteClassUseCase)
    {
        try {
            $result = $deleteClassUseCase->execute((int)$id);
            return response()->json([
                'data' => $result,
                'message' => 'Clase eliminada exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error deleting class: ' . $th->getMessage());
            return response()->json(['message' => 'Error al eliminar la clase'], 422);
        }
    }

    public function reorder(Request $request, \Promolider\Application\Infoproducts\UseCases\Course\Lesson\ReorderClassesUseCase $reorderClassesUseCase)
    {
        try {
            $orderedIds = $request->ordered_ids ?? [];
            $result = $reorderClassesUseCase->execute($orderedIds);
            return response()->json([
                'data' => $result,
                'message' => 'Clases reordenadas exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error reordering classes: ' . $th->getMessage());
            return response()->json(['message' => 'Error al reordenar las clases'], 422);
        }
    }

    public function generatePresignedUrl(Request $request)
    {
        try {
            $classId = (int) $request->class_id;
            $fileName = $request->file_name;
            $fileType = $request->file_type ?? 'application/octet-stream';
            $user = $request->user();

            $class = Clas::findOrFail($classId);
            $moduleId = $class->id_modules;
            $courseId = $class->id_courses;

            $name = Helper::formatFilename($fileName);
            $path = 'courses/' . $user->id . '/' . $courseId . '/' . $classId . '/class/' . $name;

            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ],
            ]);
            $bucket = config('filesystems.disks.s3.bucket');

            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key' => $path,
                'ACL' => 'public-read',
                'ContentType' => $fileType
            ]);

            $requestS3 = $s3Client->createPresignedRequest($cmd, '+60 minutes');
            $presignedUrl = (string) $requestS3->getUri();

            return response()->json([
                'presigned_url' => $presignedUrl,
                'path' => $path,
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error generating presigned URL: ' . $th->getMessage());
            return response()->json(['message' => 'Error al generar URL de subida'], 422);
        }
    }

    public function confirmUpload(Request $request)
    {
        try {
            $classId = (int) $request->class_id;
            $path = $request->path;
            $fileName = basename($path);

            $class = Clas::findOrFail($classId);
            
            // Reemplazar video existente o crear uno nuevo
            $video = Video::where('class_id', $classId)->first();
            if (!$video) {
                $video = new Video();
                $video->class_id = $classId;
                $video->videoable_type = 'test';
                $video->videoable_id = 0;
                $video->saved_time = 0;
            }
            
            $video->filename = $fileName;
            $video->path = $path;
            $video->save();

            return response()->json([
                'message' => 'Upload confirmed and video saved'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error confirming upload: ' . $th->getMessage());
            return response()->json(['message' => 'Error al confirmar la subida'], 422);
        }
    }
}
