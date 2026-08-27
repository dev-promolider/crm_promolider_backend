<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\CertificateTemplate;
use App\Models\Clas;
use App\Models\ClassroomPointDetail;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseGame;
use App\Models\CourseGameDetail;
use App\Models\CourseObservation;
use App\Models\CourseRate;
use App\Models\Module;
use App\Models\PurchasedCourse;
use App\Models\User;
use App\Models\UserClassroomPoint;
use App\Models\UserLessonProgress;
use Promolider\Application\Marketing\Exceptions\CourseRatingAlreadyExistsException;
use Promolider\Application\Marketing\Exceptions\CourseRatingCourseNotFoundException;
use Promolider\Application\Marketing\Exceptions\CourseRatingNotAllowedException;
use Promolider\Application\Marketing\Exceptions\CourseRatingNotFoundException;
use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentCourseRepository implements CourseRepositoryInterface
{
    public function searchCourses(string $query, ?int $userId = null, array $filters = []): array
    {
        $coursesQuery = Course::join('categories', 'categories.id', '=', 'courses.id_categories')
            ->where(function ($q) use ($query) {
                $q->where('courses.title', 'like', '%' . $query . '%')
                  ->orWhere('courses.description', 'like', '%' . $query . '%');
            })
            ->where('courses.status', 2)
            ->where('courses.marketplace_listed', 1)
            ->select(
                'courses.id',
                'courses.product_type_id',
                'courses.title',
                'courses.slug',
                'courses.description',
                'courses.price',
                'courses.course_level_id',
                'courses.url_portada',
                'courses.portada',
                'courses.user_id',
                'courses.id_categories',
                'courses.course_about',
                'courses.will_learn',
                'courses.created_at',
                'categories.name as category_name'
            );

        // Si el usuario es Socio Fundador (tipo 5), restringir a nivel 1
        if (!empty($filters['id_account_type']) && $filters['id_account_type'] == 5) {
            $coursesQuery->where('courses.course_level_id', 1);
        }

        // Excluir cursos del propio usuario si se proporciona userId
        if ($userId) {
            $coursesQuery->where('courses.user_id', '!=', $userId);
        }

        // Filtrar por tipo de producto si se especifica
        if (!empty($filters['product_type_id'])) {
            $coursesQuery->where('courses.product_type_id', $filters['product_type_id']);
        }

        // Filtrar por categoría si se especifica
        if (!empty($filters['category_id'])) {
            $coursesQuery->where('courses.id_categories', $filters['category_id']);
        }

        return $coursesQuery->distinct()
            ->orderBy('courses.created_at', 'desc')
            ->limit($filters['limit'] ?? 20)
            ->get()
            ->toArray();
    }
    // ==================== COURSES ====================

    public function listCourses(array $filters = []): array
    {
        $query = Course::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['product_type_id'])) {
            $query->where('product_type_id', $filters['product_type_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->withCount('modules')->orderBy('created_at', 'desc')->get()->toArray();
    }

    public function getCourse(int $id): ?array
    {
        $course = Course::find($id);
        return $course?->toArray();
    }

    public function createCourse(array $data): array
    {
        $course = Course::create($data);
        return $course->toArray();
    }

    public function updateCourse(int $id, array $data): ?array
    {
        $course = Course::find($id);
        if (!$course) return null;
        $course->update($data);
        $course->refresh();
        return $course->toArray();
    }

    public function deleteCourse(int $id): bool
    {
        $course = Course::find($id);
        if (!$course) return false;
        return $course->delete();
    }

    public function getCourseWithModules(int $id): ?array
    {
        $course = Course::with(['modules' => function ($q) {
            $q->orderBy('order')->with(['classes' => function ($q2) {
                $q2->orderBy('order');
            }]);
        }])->find($id);

        if (!$course) return null;

        $data = $course->toArray();
        $data['modules_count'] = $course->modules->count();
        $data['classes_count'] = $course->modules->sum(fn($m) => $m->classes->count());
        return $data;
    }

    // ==================== MODULES ====================

    public function listModules(int $courseId): array
    {
        return Module::where('id_courses', $courseId)
            ->withCount('classes')
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function getModule(int $id): ?array
    {
        $module = Module::with('classes')->find($id);
        return $module?->toArray();
    }

    public function createModule(array $data): array
    {
        $maxOrder = Module::where('id_courses', $data['id_courses'])->max('order');
        $data['order'] = ($maxOrder ?? 0) + 1;
        $data['status'] = $data['status'] ?? 0;

        $module = Module::create($data);
        return $module->toArray();
    }

    public function updateModule(int $id, array $data): ?array
    {
        $module = Module::find($id);
        if (!$module) return null;
        $module->update($data);
        $module->refresh();
        return $module->toArray();
    }

    public function deleteModule(int $id): bool
    {
        $module = Module::find($id);
        if (!$module) return false;

        DB::beginTransaction();
        try {
            // Delete all classes in this module
            Clas::where('id_modules', $id)->delete();
            $module->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reorderModules(int $courseId, array $order): bool
    {
        foreach ($order as $index => $moduleId) {
            Module::where('id', $moduleId)->where('id_courses', $courseId)->update(['order' => $index + 1]);
        }
        return true;
    }

    // ==================== CLASSES ====================

    public function listClasses(int $moduleId): array
    {
        return Clas::where('id_modules', $moduleId)->orderBy('order')->get()->toArray();
    }

    public function getClass(int $id): ?array
    {
        $class = Clas::find($id);
        return $class?->toArray();
    }

    public function createClass(array $data): array
    {
        $maxOrder = Clas::where('id_modules', $data['id_modules'])->max('order');
        $data['order'] = ($maxOrder ?? 0) + 1;
        $data['status'] = $data['status'] ?? 0;

        $class = Clas::create($data);
        return $class->toArray();
    }

    public function updateClass(int $id, array $data): ?array
    {
        $class = Clas::find($id);
        if (!$class) return null;
        $class->update($data);
        $class->refresh();
        return $class->toArray();
    }

    public function deleteClass(int $id): bool
    {
        $class = Clas::find($id);
        if (!$class) return false;
        return $class->delete();
    }

    // ==================== PROGRESS ====================

    public function getCompletedLessons(int $userId, int $courseId): array
    {
        return UserLessonProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('completed', true)
            ->pluck('lesson_id')
            ->toArray();
    }

    public function completeLesson(int $userId, int $courseId, int $lessonId): bool
    {
        UserLessonProgress::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId, 'lesson_id' => $lessonId],
            ['completed' => true, 'completed_at' => now()]
        );

        // Recalculate progress
        $totalLessons = $this->getTotalLessons($courseId);
        $completedLessons = $this->getCompletedLessons($userId, $courseId);
        $progress = $totalLessons > 0 ? round((count($completedLessons) / $totalLessons) * 100, 2) : 0;

        $this->updateCourseProgress($userId, $courseId, $progress);
        return true;
    }

    public function getCourseProgress(int $userId, int $courseId): float
    {
        $pc = PurchasedCourse::where('user_id', $userId)->where('course_id', $courseId)->first();
        return $pc ? (float) $pc->progress : 0;
    }

    public function updateCourseProgress(int $userId, int $courseId, float $progress): bool
    {
        PurchasedCourse::updateOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            [
                'progress' => $progress,
                'completed_course' => $progress >= 100 ? 1 : 0,
                'completed_date' => $progress >= 100 ? now() : null,
            ]
        );
        return true;
    }

    public function getTotalLessons(int $courseId): int
    {
        return Clas::where('id_courses', $courseId)->count();
    }

    public function syncProgress(int $userId, int $courseId): array
    {
        $completedLessons = $this->getCompletedLessons($userId, $courseId);
        $progress = $this->getCourseProgress($userId, $courseId);
        return [
            'completed_lessons' => $completedLessons,
            'progress' => $progress,
        ];
    }

    // ==================== RATINGS ====================

    public function listRatings(int $courseId): array
    {
        return CourseRate::where('course_id', $courseId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getUserRating(int $userId, int $courseId): ?array
    {
        $this->getCourseForRating($userId, $courseId);

        $rating = CourseRate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        return $rating ? $rating->toArray() : null;
    }

    public function createRating(int $userId, int $courseId, int $points, ?string $commentary): array
    {
        return DB::transaction(function () use ($userId, $courseId, $points, $commentary) {
            $course = $this->getCourseForRating($userId, $courseId, true);

            if (CourseRate::where('user_id', $userId)->where('course_id', $courseId)->exists()) {
                throw new CourseRatingAlreadyExistsException();
            }

            $rating = CourseRate::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'rate' => $points,
                'commentary' => $commentary,
            ]);

            $average = CourseRate::where('course_id', $courseId)->avg('rate');
            $course->update(['ranking_by_user' => round($average, 1)]);

            return $rating->toArray();
        });
    }

    public function updateUserRating(int $userId, int $courseId, int $points, ?string $commentary): array
    {
        return DB::transaction(function () use ($userId, $courseId, $points, $commentary) {
            $course = $this->getCourseForRating($userId, $courseId, true);

            $rating = CourseRate::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            if (!$rating) {
                throw new CourseRatingNotFoundException();
            }

            $rating->update([
                'rate' => $points,
                'commentary' => $commentary,
            ]);

            $average = CourseRate::where('course_id', $courseId)->avg('rate');
            $course->update(['ranking_by_user' => round($average, 1)]);

            return $rating->fresh()->toArray();
        });
    }

    private function getCourseForRating(int $userId, int $courseId, bool $lock = false): Course
    {
        $query = Course::select('id', 'user_id');

        if ($lock) {
            $query->lockForUpdate();
        }

        $course = $query->find($courseId);

        if (!$course) {
            throw new CourseRatingCourseNotFoundException();
        }

        if ((int) $course->user_id === $userId) {
            throw new CourseRatingNotAllowedException('El autor del curso no puede valorarlo.');
        }

        $hasCourse = PurchasedCourse::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();

        if (!$hasCourse) {
            throw new CourseRatingNotAllowedException('Solo puedes gestionar valoraciones de cursos que tienes disponibles.');
        }

        return $course;
    }

    // ==================== OBSERVATIONS ====================

    public function createObservation(array $data): array
    {
        $observation = CourseObservation::updateOrCreate(
            ['id_class' => $data['id_class'], 'id_courses' => $data['id_courses']],
            $data
        );
        return $observation->toArray();
    }

    public function listObservations(int $classId): array
    {
        return CourseObservation::where('id_class', $classId)
            ->with(['analyst:id,name', 'productor:id,name'])
            ->get()
            ->toArray();
    }

    // ==================== GAMES ====================

    public function listGames(int $courseId): array
    {
        return CourseGame::where('id_courses', $courseId)
            ->withCount('details')
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getGame(int $id): ?array
    {
        $game = CourseGame::with('details')->find($id);
        return $game?->toArray();
    }

    public function createGame(array $data): array
    {
        $game = CourseGame::create($data);
        return $game->toArray();
    }

    public function updateGame(int $id, array $data): ?array
    {
        $game = CourseGame::find($id);
        if (!$game) return null;
        $game->update($data);
        $game->refresh();
        return $game->toArray();
    }

    public function deleteGame(int $id): bool
    {
        $game = CourseGame::find($id);
        if (!$game) return false;

        DB::beginTransaction();
        try {
            CourseGameDetail::where('id_course_game', $id)->delete();
            $game->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function listGameDetails(int $gameId): array
    {
        return CourseGameDetail::where('id_course_game', $gameId)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function createGameDetail(array $data): array
    {
        $maxOrder = CourseGameDetail::where('id_course_game', $data['id_course_game'])->max('order');
        $data['order'] = ($maxOrder ?? 0) + 1;

        if (isset($data['options']) && is_string($data['options'])) {
            $data['options'] = json_decode($data['options'], true);
        }

        $detail = CourseGameDetail::create($data);
        return $detail->toArray();
    }

    public function deleteGameDetail(int $id): bool
    {
        $detail = CourseGameDetail::find($id);
        if (!$detail) return false;
        return $detail->delete();
    }

    public function getCourseExpiration(int $courseId, int $userId): ?array
    {
        $course = Course::find($courseId);
        if (!$course || !$course->months) {
            return null;
        }

        $purchased = PurchasedCourse::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->select('created_at')
            ->first();

        if (!$purchased) {
            return null;
        }

        $fechaCompra = \Carbon\Carbon::parse($purchased->created_at);
        $current = \Carbon\Carbon::now();
        $fechaVencimiento = $fechaCompra->copy()->addMonths((int) $course->months);
        $daysUntil = $current->diffInDays($fechaVencimiento, false);

        return [
            'fecha_inicio' => $fechaCompra->toDateString(),
            'fecha_vencimiento' => $fechaVencimiento->toDateString(),
            'days_until' => max(0, $daysUntil),
            'expired' => $daysUntil < 0,
            'course_title' => $course->title,
            'months' => (int) $course->months,
        ];
    }

    public function getReleasedCourses(int $userId, int $limit = 10): array
    {
        // Get user's preferred categories from preferences table
        $preferredCategories = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('preferences')) {
            $preferredCategories = \Illuminate\Support\Facades\DB::table('preferences')
                ->where('user_id', $userId)
                ->pluck('categories_id')
                ->toArray();
        }

        // Get courses the user already purchased
        $purchasedIds = PurchasedCourse::where('user_id', $userId)
            ->pluck('course_id')
            ->toArray();

        $query = Course::join('categories', 'categories.id', '=', 'courses.id_categories')
            ->join('users', 'courses.user_id', '=', 'users.id')
            ->select(
                'courses.*',
                'categories.name as category_name',
                'users.name',
                'users.last_name'
            )
            ->where('courses.status', 2)
            ->where('courses.marketplace_listed', 1)
            ->orderBy('courses.created_at', 'DESC');

        if (!empty($purchasedIds)) {
            $query->whereNotIn('courses.id', $purchasedIds);
        }

        // First attempt: filter by preferred categories
        if (!empty($preferredCategories)) {
            $results = (clone $query)
                ->whereIn('courses.id_categories', $preferredCategories)
                ->take($limit)
                ->get()
                ->toArray();

            // If less than 5 results, expand to all categories
            if (count($results) < 5) {
                $existingIds = array_column($results, 'id');
                $results = $query
                    ->whereNotIn('courses.id', $existingIds)
                    ->take($limit - count($results))
                    ->get()
                    ->toArray();
            }

            return $results;
        }

        // No preferences: return latest courses directly
        return $query->take($limit)->get()->toArray();
    }

    public function getLastPlayedCourses(int $userId, int $limit = 5): array
    {
        // Get distinct courses from UserLessonProgress ordered by last activity
        $courseIds = UserLessonProgress::where('user_id', $userId)
            ->where('completed', true)
            ->select('course_id')
            ->selectRaw('MAX(completed_at) as last_activity')
            ->groupBy('course_id')
            ->orderBy('last_activity', 'DESC')
            ->take($limit)
            ->pluck('course_id')
            ->toArray();

        if (empty($courseIds)) {
            // Fallback: use PurchasedCourse updated_at
            $purchased = PurchasedCourse::where('user_id', $userId)
                ->with('course')
                ->orderBy('updated_at', 'DESC')
                ->take($limit)
                ->get();

            return $purchased->map(function ($pc) {
                $data = $pc->course?->toArray() ?? [];
                $data['display_time'] = $pc->display_time;
                $data['last_class_reprod'] = $pc->last_class_reprod;
                $data['progress'] = $pc->progress;
                return $data;
            })->toArray();
        }

        // Preserve order from the subquery
        $ordered = implode(',', $courseIds);
        return Course::whereIn('id', $courseIds)
            ->orderByRaw(DB::raw("FIELD(id, {$ordered})"))
            ->get()
            ->toArray();
    }

    public function getGamesTop(int $courseId, int $userId): array
    {
        // Get all active game IDs for this course
        $gameIds = CourseGame::where('id_courses', $courseId)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        if (empty($gameIds)) {
            return ['top_users' => [], 'current_user' => null];
        }

        // Build ranking from classroom_point_details
        $results = ClassroomPointDetail::join(
            'user_classroom_points',
            'classroom_point_details.id_user_classroom_points',
            '=',
            'user_classroom_points.id'
        )
        ->join('users', 'users.id', '=', 'user_classroom_points.id_user')
        ->whereIn('classroom_point_details.id_course_games', $gameIds)
        ->select(
            'users.id',
            'users.name',
            'users.last_name',
            'users.photo',
            'users.username',
            DB::raw('SUM(classroom_point_details.increment_points) as total_points'),
            DB::raw('AVG(classroom_point_details.completion_time) as avg_time'),
            DB::raw('MAX(classroom_point_details.created_at) as last_played')
        )
        ->groupBy('users.id', 'users.name', 'users.last_name', 'users.photo', 'users.username')
        ->orderBy('total_points', 'DESC')
        ->orderBy('avg_time', 'ASC')
        ->get();

        // Calculate current user's position
        $currentUserData = null;
        $position = 0;
        foreach ($results as $index => $row) {
            if ((int) $row->id === $userId) {
                $position = $index + 1;
                $currentUserData = [
                    'position' => $position,
                    'total_points' => (int) $row->total_points,
                    'avg_time' => (float) $row->avg_time,
                ];
                break;
            }
        }

        return [
            'top_users' => $results->toArray(),
            'current_user' => $currentUserData,
        ];
    }

    public function getRelatedCourses(int $courseId, int $limit = 5, ?int $excludeUserId = null): array
    {
        // Get the current course to find its category
        $course = Course::find($courseId);
        if (!$course || !$course->id_categories) {
            return [];
        }

        $query = Course::join('categories', 'categories.id', '=', 'courses.id_categories')
            ->where('courses.id_categories', $course->id_categories)
            ->where('courses.id', '!=', $courseId)
            ->where('courses.status', 2)
            ->where('courses.marketplace_listed', 1)
            ->select(
                'courses.id',
                'courses.product_type_id',
                'courses.title',
                'courses.slug',
                'courses.description',
                'courses.price',
                'courses.course_level_id',
                'courses.url_portada',
                'courses.portada',
                'courses.user_id',
                'courses.id_categories',
                'courses.course_about',
                'courses.will_learn',
                'courses.ranking_by_user',
                'courses.created_at',
                'categories.name as category_name'
            );

        // Excluir cursos del usuario especificado
        if ($excludeUserId) {
            $query->where('courses.user_id', '!=', $excludeUserId);
        }

        $results = $query->distinct()
            ->orderBy('courses.ranking_by_user', 'desc')
            ->orderBy('courses.created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();

        // If less than limit results, expand to other categories
        if (count($results) < $limit) {
            $existingIds = array_column($results, 'id');
            $existingIds[] = $courseId;

            $additional = Course::join('categories', 'categories.id', '=', 'courses.id_categories')
                ->whereNotIn('courses.id', $existingIds)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->select(
                    'courses.id',
                    'courses.product_type_id',
                    'courses.title',
                    'courses.slug',
                    'courses.description',
                    'courses.price',
                    'courses.course_level_id',
                    'courses.url_portada',
                    'courses.portada',
                    'courses.user_id',
                    'courses.id_categories',
                    'courses.course_about',
                    'courses.will_learn',
                    'courses.ranking_by_user',
                    'courses.created_at',
                    'categories.name as category_name'
                );

            if ($excludeUserId) {
                $additional->where('courses.user_id', '!=', $excludeUserId);
            }

            $additionalResults = $additional->distinct()
                ->orderBy('courses.ranking_by_user', 'desc')
                ->orderBy('courses.created_at', 'desc')
                ->limit($limit - count($results))
                ->get()
                ->toArray();

            $results = array_merge($results, $additionalResults);
        }

        return $results;
    }

    // ==================== CERTIFICATES ====================

    public function listCertificates(int $userId): array
    {
        return CourseCertificate::where('user_id', $userId)
            ->with(['course:id,title', 'template:id,name'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function createCertificate(array $data): array
    {
        $cert = CourseCertificate::create($data);
        return $cert->toArray();
    }

    public function getCertificate(int $id): ?array
    {
        $cert = CourseCertificate::with(['course', 'template'])->find($id);
        return $cert?->toArray();
    }

    public function listTemplates(): array
    {
        return CertificateTemplate::active()->orderBy('name')->get()->toArray();
    }

    public function createTemplate(array $data): array
    {
        $template = CertificateTemplate::create($data);
        return $template->toArray();
    }

    public function updateTemplate(int $id, array $data): ?array
    {
        $template = CertificateTemplate::find($id);
        if (!$template) return null;
        $template->update($data);
        $template->refresh();
        return $template->toArray();
    }
}
