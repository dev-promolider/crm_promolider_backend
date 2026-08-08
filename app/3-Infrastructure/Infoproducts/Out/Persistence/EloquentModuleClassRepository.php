<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Entities\Course\Clas as ClassEntity;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use App\Models\Clas as EloquentClass;
use App\Models\Video as EloquentVideo;

class EloquentModuleClassRepository implements ModuleClassRepositoryInterface
{
    public function findClassesByModuleId(int $moduleId): array
    {
        $lessons = EloquentClass::with('video')
                                ->where('id_modules', $moduleId)
                                ->orderBy('order', 'asc')
                                ->get()
                                ->map(function ($lesson) {
                                    $video = $lesson->video;
                                    $lesson->has_video = $video ? true : false;
                                    $lesson->video_url = $video ? 'https://promolider-storage-user.s3.sa-east-1.amazonaws.com/' . ltrim($video->path, '/') : null;
                                    return $lesson;
                                });

        return $lessons->map(function ($lesson) {
            return new ClassEntity(
                $lesson->id,
                $lesson->id_modules,
                $lesson->name,
                $lesson->slug,
                $lesson->time,
                $lesson->description,
                $lesson->url,
                $lesson->order,
                $lesson->status,
                $lesson->progress,
                $lesson->has_video,
                $lesson->video_url
            );
        })->toArray();
    }
}
