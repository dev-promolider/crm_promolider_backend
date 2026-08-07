<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Lesson;

use App\Models\Clas;
use App\Models\Module;
use App\Models\Video;
use App\Models\Course;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Exception;

class UpdateClassUseCase
{
    public function execute(int $classId, array $data, $user, $files = null): array
    {
        try {
            DB::beginTransaction();

            $class = Clas::findOrFail($classId);
            $module = Module::findOrFail($class->id_modules);
            $course = Course::findOrFail($module->id_courses);

            if (isset($data['title'])) {
                $class->name = $data['title'];
                $class->slug = Str::slug($data['title']);
            }
            if (isset($data['description'])) {
                $class->description = $data['description'];
            }
            
            if (!$class->save()) {
                throw new Exception("Error al actualizar la clase");
            }

            // Handle video upload to S3 if a NEW file is present
            if ($files && is_array($files)) {
                $file = $files[0];
                
                $name = Helper::formatFilename($file->getClientOriginalName());
                $path = 'courses/' . $user->id . '/' . $course->id . '/' . $class->id . '/class/';

                // Initialize S3 Client using config
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

                // Delete old video record if exists
                $oldVideo = Video::where('class_id', $class->id)->first();
                if ($oldVideo) {
                    // Optional: delete from S3
                    // Storage::disk('s3')->delete($oldVideo->path);
                    $oldVideo->delete();
                }

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
                'class' => $class->toArray()
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error updating class: ' . $th->getMessage());
            throw $th;
        }
    }
}
