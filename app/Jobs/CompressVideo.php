<?php

namespace App\Jobs;

use App\Models\Clas;
use App\Models\Video;
use App\Helpers\Helper;
use App\Events\LoadingVideo;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class CompressVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;
    protected $courseId;
    protected $classId;

    public function __construct($filePath, $userId, $courseId, $classId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->courseId = $courseId;
        $this->classId = $classId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Formatear el nombre del archivo
            $name = Helper::formatFilename(basename($this->filePath));  // Utiliza basename para obtener solo el nombre del archivo

            // Ruta local temporal donde se almacenará el video original
            $localOriginalPath = 'temp/original_' . $name;

            // Ruta local temporal donde se almacenará el video comprimido
            $localCompressedPath = 'temp/compressed_' . $name;

            // Mover el archivo original a la ubicación deseada en el disco 'local'
            Storage::disk('local')->move($this->filePath, $localOriginalPath);

            // Comprimir el video utilizando php-ffmpeg
            $ffmpeg = FFMpeg::fromDisk('local');
            $video = $ffmpeg->open($localOriginalPath);

            $format = new X264('aac', 'libx264');
            $format->setKiloBitrate(500); // Ajusta el valor según tus necesidades
            $format->setAdditionalParameters(['-crf', '23']); // Ajusta el valor según tus necesidades
            $video->export()
                ->inFormat($format)
                ->save($localCompressedPath);

            // Subir el video comprimido a S3 desde el disco 'local'
            $path = 'courses/' . $this->userId . '/' . $this->courseId . '/' . $this->classId . '/class/';
            $s3Path = $path . 'compressed_' . $name;

            $compressedContent = Storage::disk('local')->get($localCompressedPath);
            
            Storage::disk('s3')->put($s3Path, $compressedContent, 'public');

            unlink(storage_path('app/temp/original_' . $name));
            unlink(storage_path('app/temp/compressed_' . $name));

            // Crear y guardar el registro en la base de datos
            $videoModel = new Video();
            $videoModel->filename = 'compressed_' . $name;
            $videoModel->path = $s3Path;
            $videoModel->videoable_type = 'test';
            $videoModel->videoable_id = 0;
            $videoModel->class_id = $this->classId;
            $videoModel->saved_time = 0;
            $videoModel->save();

            $Class = Clas::find($videoModel->class_id);
            $Class->update(['progress' => 1]);

        } catch (\Throwable $th) {
            Log::error("Ocurrio un error", [$th->getMessage()]);
        }
    }
}
