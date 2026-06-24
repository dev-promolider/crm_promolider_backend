<?php

namespace App\Jobs;

use App\Models\Video;
use App\Helpers\Helper;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class UpdateCompressVideo implements ShouldQueue
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
            
            $name = Helper::formatFilename(basename($this->filePath));

            $localOriginalPath = 'temp/original_' . $name;

            $localCompressedPath = 'temp/compressed_' . $name;

            Storage::disk('local')->move($this->filePath, $localOriginalPath);

            $ffmpeg = FFMpeg::fromDisk('local');
            $videoCmp = $ffmpeg->open($localOriginalPath);

            $format = new X264('aac', 'libx264');
            $format->setKiloBitrate(500); // Ajusta el valor según tus necesidades
            $format->setAdditionalParameters(['-crf', '23']); // Ajusta el valor según tus necesidades
            $videoCmp->export()
                ->inFormat($format)
                ->save($localCompressedPath);

            $path = 'courses/' . $this->userId . '/' . $this->courseId . '/' . $this->classId . '/' . 'class/';

            $s3Path = $path . 'compressed_' . $name;
            $compressedContent = Storage::disk('local')->get($localCompressedPath);

            $video = Video::where('class_id', $this->classId)->get()->first();

            Storage::disk('s3')->delete($video->path);
            Storage::disk('s3')->put($s3Path, $compressedContent, 'public');

            unlink(storage_path('app/temp/original_' . $name));
            unlink(storage_path('app/temp/compressed_' . $name));

            $video->path = $s3Path;
            $video->filename= 'compressed_' . $name;
            $video->videoable_type = 'test';
            $video->videoable_id = 0;
            $video->saved_time = 0;
            $video->update();

        } catch (\Throwable $th) {
            Log::error("Ocurrio un error", [$th->getMessage()]);
        }
    }
}
