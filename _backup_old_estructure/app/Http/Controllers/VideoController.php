<?php

namespace App\Http\Controllers;

use App\Models\Clas;
use Aws\S3\S3Client;
use App\Models\Video;
use App\Models\Course;
use App\Models\Module;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Aws\S3\MultipartUploader;
use App\Traits\ResponseFormat;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Aws\Exception\MultipartUploadException;

class VideoController extends Controller
{
    use ResponseFormat;

    public function updateStatus(Request $request)
    {
        $video = Video::findOrFail($request->id);
        $video->status = 1;
        $video->update();
    }

    public function streamVideo(Request $request)
    {
        $video = Video::where('class_id', $request->class_id)
            ->select('path')
            ->first();
    
        if (!$video) {
            return response()->json([
                'message' => 'Video no encontrado para esta clase',
            ], 404);
        }
    
        $path = "https://promolider-storage-user.s3-accelerate.amazonaws.com/" . $video->path;
        return response()->json([
            'data' => $path,
            'message' => 'Url recuperada con exito'
        ]);
    }

    public function saveTime(Request $request)
    {
        $video = Video::findOrFail($request->id);
        $video->saved_time = $request->time;
        $video->save();

        return $this->responseOk('', 'video time saved');
    }

    public function showTime(Request $request)
    {
        $video = Video::findOrFail($request->id);
        $video_time = $video->saved_time;

        return $this->responseOk('', $video_time);
    }

    public function updateVideo($id,$filename)
    {
        $user = Auth::user();
        $module_id = Clas::where('id', $id)->get()->first()->id_modules;
        $actual_module = Module::where('id', $module_id)->get()->first();
        $actual_course = Course::where('id', $actual_module->id_courses)->get()->first();

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'use_accelerate_endpoint' => true,
        ]);
       
        $course_id = $actual_course->id;

        $path = 'courses/' . $user->id . '/' . $course_id . '/' . $id . '/' . 'class/';

        try {
            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $path . $filename,
                'ACL' => 'public-read',
            ]);

            $url = $path . $filename;

            $videoPath = Video::where('class_id', $id)->first();
            if(!!$videoPath){
                $s3Client->deleteObject([
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $videoPath->path,
                ]);

                $videoPath->filename = $filename;
                $videoPath->path = $url;
                $videoPath->videoable_type = 'test';
                $videoPath->videoable_id = 0;
                $videoPath->saved_time = 0;
                $videoPath->update();

            }else{
                $video = new Video();
                $video->filename = $filename;
                $video->path = $url;
                $video->videoable_type = 'test';
                $video->videoable_id = 0;
                $video->class_id = $id;
                $video->saved_time = 0;
                $video->save();
            }

            $presignedUrl = $s3Client->createPresignedRequest($cmd, '+15 minutes')->getUri()->__toString();

            return response()->json([
                'data' => $presignedUrl,
                'message' => 'Url generada'
            ]);
        } catch (S3Exception $e) {
            // Manejar la excepción según tus necesidades
            Log::error('Error generando URL firmada: ' . $e->getMessage());
            return null;
        }
    }

    public static function generatePresignedUrl()
    {
        $path = 'courses/class/';
        $filename = 'nombre_del_video.mp4';

        $s3Client = new S3Client([
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
                'Key' => $path . $filename,
                'ACL' => 'public-read',
            ]);

            $presignedUrl = $s3Client->createPresignedRequest($cmd, '+15 minutes')->getUri()->__toString();

            return response()->json([
                'data' => $presignedUrl,
                'message' => 'Url generada'
            ]);
        } catch (S3Exception $e) {
            // Manejar la excepción según tus necesidades
            Log::error('Error generando URL firmada: ' . $e->getMessage());
            return null;
        }
    }

    public static function storeClassVideo($file, $user_id, $course_id, $class_id)
    {
        $name = Helper::formatFilename($file->getClientOriginalName());
        $path = 'courses/' . $user_id . '/' . $course_id . '/' . $class_id . '/class/';

        // Configuración de AWS
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'use_accelerate_endpoint' => true,
        ]);

        // Iniciar la carga multipartida
        $uploader = new MultipartUploader($s3Client, $file->getRealPath(), [
            'bucket' => env('AWS_BUCKET'),
            'key' => $path . $name,
            'ACL' => 'public-read',
        ]);

        try {
            $result = $uploader->upload();
            $url = $path . $name;

            // Guardar en la base de datos
            $video = new Video();
            $video->filename = $name;
            $video->path = $url;
            $video->videoable_type = 'test';
            $video->videoable_id = 0;
            $video->class_id = $class_id;
            $video->saved_time = 0;
            $video->save();

            Log::info(['ruta del arcivo subida', [$result['ObjectURL']]]);
        } catch (MultipartUploadException $e) {
            Log::info('Ocurrio un error', [$e->getMessage()]);
        }
    }

    public static function deleteClassVideo($video)
    {
        if (count($video) > 0) {
            Storage::disk('s3')->delete($video->first()->path);
            $video->first()->delete();
        }
    }

    public static function updateClassVideo($file, $user_id, $course_id, $class_id)
    {
        $video = Video::where('class_id', $class_id)->first();

        // Eliminar el antiguo video en S3
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $s3Client->deleteObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $video->path,
        ]);

        // Subir el nuevo video a S3
        $name = Helper::formatFilename($file->getClientOriginalName());
        $path = 'courses/' . $user_id . '/' . $course_id . '/' . $class_id . '/' . 'class/';

        $uploader = new MultipartUploader($s3Client, $file->getRealPath(), [
            'bucket' => env('AWS_BUCKET'),
            'key' => $path . $name,
            'ACL' => 'public-read',
        ]);

        try {
            $result = $uploader->upload();
            $url = $path . $name;

            // Actualizar en la base de datos
            $video->filename = $name;
            $video->path = $url;
            $video->videoable_type = 'test';
            $video->videoable_id = 0;
            $video->saved_time = 0;
            $video->update();

            Log::info(['ruta del archivo actualizado', [$result['ObjectURL']]]);
        } catch (MultipartUploadException $e) {
            Log::info('Ocurrió un error al actualizar el archivo', [$e->getMessage()]);
        }
    }

}
