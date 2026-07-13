<?php

namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use App\Models\Clas;
use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class StorePurchasedCourseUseCase
{
    public function __construct(
        private PurchasedCourseRepositoryInterface $repository
    ) {}

    public function execute(int $userId, int $courseId): array
    {
        // Get all classes for this course
        $classes = Clas::join('modules', 'class.id_modules', '=', 'modules.id')
            ->where('modules.id_courses', $courseId)
            ->select('class.id')
            ->get();

        // Build initial classes_status array
        $classesStatus = [];
        foreach ($classes as $class) {
            $classesStatus[] = [$class->id, 'NOT SEEN'];
        }

        return $this->repository->create($userId, $courseId, $classesStatus);
    }
}
