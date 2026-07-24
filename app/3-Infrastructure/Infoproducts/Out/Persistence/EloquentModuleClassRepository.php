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
        $lessons = EloquentClass::where('id_modules', $moduleId)
                                ->orderBy('order', 'asc')
                                ->get()
                                ->map(function ($lesson) {
                                    $lesson->has_video = EloquentVideo::where('class_id', $lesson->id)->exists();
                                    return $lesson;
                                });

        return $lessons->map(function ($lesson) {
            return new ClassEntity(
                $lesson->id,
                $lesson->id_modules,
                $lesson->name,
                $lesson->slug,
                $lesson->time,
                $lesson->url,
                $lesson->description,
                $lesson->order,
                $lesson->status,
                $lesson->progress,
                $lesson->has_video
            );
        })->toArray();
    }
}
