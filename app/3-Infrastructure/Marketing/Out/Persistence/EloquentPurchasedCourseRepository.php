<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\PurchasedCourse;
use App\Models\Clas;
use App\Models\User;
use Promolider\Domain\Marketing\Ports\Out\PurchasedCourseRepositoryInterface;

class EloquentPurchasedCourseRepository implements PurchasedCourseRepositoryInterface
{
    public function findByUserAndCourse(int $userId, int $courseId): ?array
    {
        $record = PurchasedCourse::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        return $record ? $record->toArray() : null;
    }

    public function create(int $userId, int $courseId, array $classesStatus): array
    {
        $record = new PurchasedCourse();
        $record->user_id = $userId;
        $record->course_id = $courseId;
        $record->classes_status = json_encode($classesStatus);
        $record->progress = 0;
        $record->completed_course = false;
        $record->save();

        return $record->toArray();
    }

    public function updateClassStatus(int $userId, int $courseId, int $classId, string $status): void
    {
        $record = PurchasedCourse::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$record) return;

        $classesStatus = json_decode($record->classes_status, true);
        if (!is_array($classesStatus)) return;

        $updated = [];
        $allSeen = true;

        foreach ($classesStatus as $item) {
            if (isset($item[0]) && (int)$item[0] === $classId) {
                $item[1] = $status;
            }
            $updated[] = $item;
            if (isset($item[1]) && $item[1] !== 'SEEN') {
                $allSeen = false;
            }
        }

        $record->classes_status = json_encode($updated);
        $record->completed_course = $allSeen;
        $record->save();
    }

    public function saveClassSeen(int $userId, int $courseId, int $classId, ?string $displayTime): void
    {
        $record = PurchasedCourse::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$record) return;

        $record->last_class_reprod = $classId;

        if ($displayTime !== null) {
            $record->display_time = $displayTime;
        }

        // Update classes_status with time info
        $classesStatus = $record->classes_status ? json_decode($record->classes_status, true) : [];

        if (!is_array($classesStatus)) {
            $classesStatus = [];
        }

        $classesStatus[(string)$classId] = [
            'time' => $displayTime,
        ];

        $record->classes_status = json_encode($classesStatus);
        $record->save();
    }

    public function getClassTime(int $userId, int $courseId, int $classId): ?array
    {
        $record = PurchasedCourse::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$record) return null;

        $classesStatus = json_decode($record->classes_status, true);

        if (!is_array($classesStatus) || !isset($classesStatus[(string)$classId])) {
            return ['time' => 0];
        }

        return $classesStatus[(string)$classId];
    }

    public function getLastClassPlayed(int $userId, int $courseId): ?array
    {
        $record = PurchasedCourse::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if (!$record || !$record->last_class_reprod) {
            return null;
        }

        $class = Clas::select('id', 'name')
            ->where('id', $record->last_class_reprod)
            ->first();

        if (!$class) return null;

        return [
            'id' => $class->id,
            'name' => $class->name,
            'display_time' => $record->display_time,
        ];
    }

    public function getCompletedCourses(int $userId): array
    {
        $user = User::select('id', 'username', 'name', 'last_name')
            ->with(['purchaseds' => function ($query) {
                $query->where('completed_course', true);
            }, 'purchaseds.course:id,title,course_time'])
            ->find($userId);

        return $user ? $user->toArray() : [];
    }

    public function getClassesStatus(int $userId, int $courseId): ?array
    {
        $record = PurchasedCourse::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->select('classes_status')
            ->first();

        if (!$record) return null;

        $decoded = json_decode($record->classes_status, true);

        if (!is_array($decoded)) return null;

        $classIds = [];
        $statuses = [];

        foreach ($decoded as $item) {
            if (isset($item[0])) $classIds[] = $item[0];
            if (isset($item[1])) $statuses[] = $item[1];
        }

        return [
            'class_ids' => $classIds,
            'statuses' => $statuses,
        ];
    }
}
