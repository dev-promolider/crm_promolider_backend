<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Lesson;

use App\Models\Clas;
use App\Models\Module;
use App\Models\Video;
use App\Models\Course;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Exception;

class CreateClassUseCase
{
    public function execute(int $moduleId, array $data, $user, $files = null): array
    {
        try {
            DB::beginTransaction();

            $module = Module::findOrFail($moduleId);
            $course = Course::findOrFail($module->id_courses);

            $maxOrder = Clas::where('id_modules', $moduleId)->max('order');
            $maxOrder = $maxOrder ?? 0;

            $class = new Clas();
            $class->id_modules = $moduleId;
            $class->name = $data['title'];
            $class->slug = Str::slug($data['title']);
            $class->description = $data['description'] ?? 'Sin descripción';
            $class->time = '00:00:00'; // Default, maybe updated later
            $class->url = '/class/example'; // Default value
            $class->status = 0;
            $class->order = $maxOrder + 1;
            
            if (!$class->save()) {
                throw new Exception("Error al guardar la clase");
            }

            // Handle video upload to S3 if a file is present
            if ($files && is_array($files)) {
                // Take the first file as the video
                $file = $files[0];
                
                $name = Helper::formatFilename($file->getClientOriginalName());
                $path = 'courses/' . $user->id . '/' . $course->id . '/' . $class->id . '/class/';

                // Initialize S3 Client using config instead of env directly
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region' => config('filesystems.disks.s3.region'),
                    'credentials' => [
                        'key' => config('filesystems.disks.s3.key'),
                        'secret' => config('filesystems.disks.s3.secret'),
                    ],
                ]);
                $bucket = config('filesystems.disks.s3.bucket');

                // Upload to S3 using MultipartUploader
                $uploader = new MultipartUploader($s3Client, $file->getRealPath(), [
                    'bucket' => $bucket,
                    'key' => $path . $name,
                    'ACL' => 'public-read',
                ]);

                $result = $uploader->upload();
                $url = $path . $name;

                $video = new Video();
                $video->filename = $name;
                $video->path = $url;
                $video->videoable_type = 'test';
                $video->videoable_id = 0;
                $video->class_id = $class->id;
                $video->saved_time = 0;
                $video->save();
            }

            DB::commit();

            return [
                'status' => 'ok',
                'classes' => Clas::where('id_modules', $moduleId)->orderBy('order', 'asc')->get()->toArray()
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error creating class: ' . $th->getMessage());
            throw $th;
        }
    }
}
