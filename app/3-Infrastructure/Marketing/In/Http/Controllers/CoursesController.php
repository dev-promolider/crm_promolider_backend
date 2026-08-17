<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\ClassroomPointDetail;
use App\Models\Course;
use App\Models\CourseGame;
use App\Models\Module;
use App\Models\PurchasedCourse;
use App\Models\UserClassroomPoint;
use App\Models\UserGame;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Courses\CourseUseCase;
use Promolider\Application\Marketing\UseCases\Courses\SearchCoursesUseCase;
use Promolider\Application\Marketing\UseCases\Courses\GetRelatedCoursesUseCase;
use Promolider\Application\Marketing\UseCases\Courses\ListGameCommentsUseCase;
use Promolider\Application\Marketing\UseCases\Courses\CreateGameCommentUseCase;
use Promolider\Application\Marketing\UseCases\Courses\GetCourseExpirationUseCase;
use Promolider\Application\Marketing\UseCases\Courses\GetReleasedCoursesUseCase;
use Promolider\Application\Marketing\UseCases\Courses\GetLastPlayedCoursesUseCase;
use App\Models\LatestLessons;
use Promolider\Application\Marketing\UseCases\Courses\GetGamesTopUseCase;

class CoursesController extends Controller
{
    public function __construct(
        private CourseUseCase $courseUseCase,
        private SearchCoursesUseCase $searchCoursesUseCase,
        private GetRelatedCoursesUseCase $getRelatedCoursesUseCase,
        private ListGameCommentsUseCase $listGameCommentsUseCase,
        private CreateGameCommentUseCase $createGameCommentUseCase,
        private GetCourseExpirationUseCase $getCourseExpirationUseCase,
        private GetReleasedCoursesUseCase $getReleasedCoursesUseCase,
        private GetLastPlayedCoursesUseCase $getLastPlayedCoursesUseCase,
        private GetGamesTopUseCase $getGamesTopUseCase,
    ) {}

    // ==================== COURSES ====================

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'product_type_id', 'search']);
            $result = $this->courseUseCase->listCourses($filters);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error listing courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar cursos'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->courseUseCase->getCourse($id);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Curso no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error showing course: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener curso'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'product_type_id' => 'required|integer',
                'price' => 'nullable|numeric|min:0',
                'id_categories' => 'nullable|integer|exists:categories,id',
                'course_level_id' => 'nullable|integer',
                'certificate' => 'nullable|boolean',
                'will_learn' => 'nullable|string',
                'prev_knowledge' => 'nullable|string',
                'course_about' => 'nullable|string',
                'image' => 'nullable|string|max:255',
            ]);

            $result = $this->courseUseCase->createCourse($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating course: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear curso'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'nullable|numeric|min:0',
                'id_categories' => 'nullable|integer',
                'certificate' => 'nullable|boolean',
                'status' => 'nullable|integer',
                'image' => 'nullable|string|max:255',
            ]);

            $result = $this->courseUseCase->updateCourse($id, $validated);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Curso no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error updating course: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar curso'], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate(['q' => 'required|string|min:2|max:100']);
            $query = $request->input('q');
            $filters = $request->only(['product_type_id', 'category_id', 'limit', 'id_account_type']);
            $userId = $request->user()?->id;

            $result = $this->searchCoursesUseCase->execute($query, $userId, $filters);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Query de búsqueda inválida', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error searching courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al buscar cursos'], 500);
        }
    }

    public function expiration(int $courseId, Request $request): JsonResponse
    {
        try {
            $result = $this->getCourseExpirationUseCase->execute($courseId, $request->user()->id);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'No se encontró información de expiración'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error getting course expiration: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener expiración del curso'], 500);
        }
    }

    public function relatedCourses(int $courseId, Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->input('limit', 5);
            $excludeUserId = $request->user()?->id;
            $result = $this->getRelatedCoursesUseCase->execute($courseId, $limit, $excludeUserId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error getting related courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener cursos relacionados'], 500);
        }
    }

    public function released(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->input('limit', 10);
            $result = $this->getReleasedCoursesUseCase->execute($request->user()->id, $limit);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error getting released courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener cursos liberados'], 500);
        }
    }

    public function lastPlayed(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->input('limit', 5);
            $result = $this->getLastPlayedCoursesUseCase->execute($request->user()->id, $limit);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error getting last played courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener últimos cursos'], 500);
        }
    }

    public function gamesTop(int $courseId, Request $request): JsonResponse
    {
        try {
            $result = $this->getGamesTopUseCase->execute($courseId, $request->user()->id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error getting games top: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener top de juegos'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->courseUseCase->deleteCourse($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Curso no encontrado'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Curso eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting course: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar curso'], 500);
        }
    }

    // ==================== MODULES ====================

    public function modulesIndex(int $courseId): JsonResponse
    {
        try {
            $modules = $this->courseUseCase->listModules($courseId);
            return response()->json(['success' => true, 'data' => $modules]);
        } catch (\Exception $e) {
            Log::error('Error listing modules: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar módulos'], 500);
        }
    }

    public function modulesStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_courses' => 'required|integer|exists:courses,id',
                'name' => 'required|string|max:255',
            ]);

            $result = $this->courseUseCase->createModule($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating module: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear módulo'], 500);
        }
    }

    public function modulesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate(['name' => 'required|string|max:255']);
            $result = $this->courseUseCase->updateModule($id, $validated);
            if (!$result) return response()->json(['success' => false, 'message' => 'Módulo no encontrado'], 404);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error updating module: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar módulo'], 500);
        }
    }

    public function modulesDestroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->courseUseCase->deleteModule($id);
            if (!$deleted) return response()->json(['success' => false, 'message' => 'Módulo no encontrado'], 404);
            return response()->json(['success' => true, 'message' => 'Módulo eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting module: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar módulo'], 500);
        }
    }

    public function modulesReorder(Request $request, int $courseId): JsonResponse
    {
        try {
            $validated = $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:modules,id']);
            $this->courseUseCase->reorderModules($courseId, $validated['order']);
            return response()->json(['success' => true, 'message' => 'Orden actualizado']);
        } catch (\Exception $e) {
            Log::error('Error reordering modules: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al reordenar módulos'], 500);
        }
    }

    // ==================== CLASSES ====================

    public function classesIndex(int $moduleId): JsonResponse
    {
        try {
            $classes = $this->courseUseCase->listClasses($moduleId);
            return response()->json(['success' => true, 'data' => $classes]);
        } catch (\Exception $e) {
            Log::error('Error listing classes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar clases'], 500);
        }
    }

    public function classesStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_courses' => 'required|integer|exists:courses,id',
                'id_modules' => 'required|integer|exists:modules,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'video' => 'nullable|string|max:255',
                'time' => 'nullable|integer|min:0',
            ]);

            $result = $this->courseUseCase->createClass($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear clase'], 500);
        }
    }

    public function classesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'video' => 'nullable|string|max:255',
                'time' => 'nullable|integer|min:0',
                'status' => 'nullable|integer',
            ]);

            $result = $this->courseUseCase->updateClass($id, $validated);
            if (!$result) return response()->json(['success' => false, 'message' => 'Clase no encontrada'], 404);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error updating class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar clase'], 500);
        }
    }

    public function classesDestroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->courseUseCase->deleteClass($id);
            if (!$deleted) return response()->json(['success' => false, 'message' => 'Clase no encontrada'], 404);
            return response()->json(['success' => true, 'message' => 'Clase eliminada']);
        } catch (\Exception $e) {
            Log::error('Error deleting class: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar clase'], 500);
        }
    }

    // ==================== PROGRESS ====================

    public function getProgress(int $courseId, Request $request): JsonResponse
    {
        try {
            $result = $this->courseUseCase->getProgress($request->user()->id, $courseId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error getting progress: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener progreso'], 500);
        }
    }

    public function completeLesson(Request $request, int $courseId, int $lessonId): JsonResponse
    {
        try {
            $completed = $this->courseUseCase->completeLesson($request->user()->id, $courseId, $lessonId);
            return response()->json(['success' => $completed]);
        } catch (\Exception $e) {
            Log::error('Error completing lesson: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al completar lección'], 500);
        }
    }

    public function updateProgress(Request $request, int $courseId): JsonResponse
    {
        try {
            $validated = $request->validate(['progress' => 'required|numeric|min:0|max:100']);
            $updated = $this->courseUseCase->updateProgress($request->user()->id, $courseId, $validated['progress']);
            return response()->json(['success' => $updated]);
        } catch (\Exception $e) {
            Log::error('Error updating progress: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar progreso'], 500);
        }
    }

    // ==================== RATINGS ====================

    public function ratingsIndex(int $courseId): JsonResponse
    {
        try {
            $ratings = $this->courseUseCase->listRatings($courseId);
            return response()->json(['success' => true, 'data' => $ratings]);
        } catch (\Exception $e) {
            Log::error('Error listing ratings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar valoraciones'], 500);
        }
    }

    public function ratingsStore(Request $request, int $courseId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'points' => 'required|integer|min:1|max:5',
                'commentary' => 'nullable|string|max:500',
            ]);

            $result = $this->courseUseCase->createRating($request->user()->id, $courseId, $validated['points'], $validated['commentary'] ?? null);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating rating: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear valoración'], 500);
        }
    }

    // ==================== OBSERVATIONS ====================

    public function observationsStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_class' => 'required|integer|exists:classes,id',
                'id_courses' => 'required|integer|exists:courses,id',
                'observation' => 'required|string',
            ]);

            $data = array_merge($validated, [
                'id_analyst' => $request->user()->id,
                'id_productor' => $request->input('id_productor', $request->user()->id),
                'status' => 0,
            ]);

            $result = $this->courseUseCase->createObservation($data);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating observation: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear observación'], 500);
        }
    }

    public function observationsList(int $classId): JsonResponse
    {
        try {
            $observations = $this->courseUseCase->listObservations($classId);
            return response()->json(['success' => true, 'data' => $observations]);
        } catch (\Exception $e) {
            Log::error('Error listing observations: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar observaciones'], 500);
        }
    }

    // ==================== GAMES ====================

    /**
     * GET /marketing/courses/{courseId}/dinamicas
     * Lista las dinámicas activas de un curso (con tipo de juego y clasificación).
     * Reemplaza GameController::list($id) del monolito.
     */
    public function dinamicasList(int $courseId): JsonResponse
    {
        try {
            $dinamicas = CourseGame::join('games_types', 'course_games.game_type_id', '=', 'games_types.id')
                ->select('course_games.*', 'games_types.title as type1')
                ->where('course_games.status', 1)
                ->where('course_games.course_id', $courseId)
                ->get();

            foreach ($dinamicas as $valor) {
                if ($valor->course_id != null && $valor->module_id == null && $valor->lesson_id == null) {
                    $valor->type2 = 1; // Dinámica de curso
                } elseif ($valor->course_id != null && $valor->module_id != null && $valor->lesson_id == null) {
                    $valor->type2 = 2; // Dinámica de módulo
                } elseif ($valor->course_id != null && $valor->module_id == null && $valor->lesson_id != null) {
                    $valor->type2 = 3; // Dinámica de clase
                }
            }

            return response()->json(['success' => true, 'data' => $dinamicas]);
        } catch (\Exception $e) {
            Log::error('Error listing dinamicas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar dinámicas'], 500);
        }
    }

    /**
     * GET /marketing/courses/dinamicas/{gameId}/data
     * Obtiene los datos/detalle de una dinámica específica.
     * Reemplaza GameController::datos($id) del monolito.
     */
    public function dinamicaData(int $gameId): JsonResponse
    {
        try {
            $data = CourseGameDetail::where('game_id', $gameId)->first();
            if (!$data) {
                return response()->json(['success' => true, 'data' => null, 'message' => 'Sin datos']);
            }
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting dinamica data: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener datos'], 500);
        }
    }

    public function gamesIndex(int $courseId): JsonResponse
    {
        try {
            $games = $this->courseUseCase->listGames($courseId);
            return response()->json(['success' => true, 'data' => $games]);
        } catch (\Exception $e) {
            Log::error('Error listing games: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar juegos'], 500);
        }
    }

    public function gamesStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_courses' => 'required|integer|exists:courses,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'nullable|string|max:50',
            ]);

            $result = $this->courseUseCase->createGame($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating game: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear juego'], 500);
        }
    }

    public function gamesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'config' => 'nullable|json',
            ]);

            $result = $this->courseUseCase->updateGame($id, $validated);
            if (!$result) return response()->json(['success' => false, 'message' => 'Juego no encontrado'], 404);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error updating game: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar juego'], 500);
        }
    }

    public function gamesDestroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->courseUseCase->deleteGame($id);
            if (!$deleted) return response()->json(['success' => false, 'message' => 'Juego no encontrado'], 404);
            return response()->json(['success' => true, 'message' => 'Juego eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting game: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar juego'], 500);
        }
    }

    public function gameDetailsIndex(int $gameId): JsonResponse
    {
        try {
            $details = $this->courseUseCase->listGameDetails($gameId);
            return response()->json(['success' => true, 'data' => $details]);
        } catch (\Exception $e) {
            Log::error('Error listing game details: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar detalles'], 500);
        }
    }

    public function gameDetailsStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_course_game' => 'required|integer|exists:course_games,id',
                'question' => 'required|string',
                'answer' => 'nullable|string',
                'options' => 'nullable|json',
                'points' => 'nullable|integer|min:0',
            ]);

            $result = $this->courseUseCase->createGameDetail($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating game detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear detalle'], 500);
        }
    }

    public function gameDetailsDestroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->courseUseCase->deleteGameDetail($id);
            if (!$deleted) return response()->json(['success' => false, 'message' => 'Detalle no encontrado'], 404);
            return response()->json(['success' => true, 'message' => 'Detalle eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting game detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar detalle'], 500);
        }
    }

    // ==================== ACTIVE GAME ====================

    /**
     * POST /marketing/courses/game/active
     * Obtiene los juegos activos para un curso/módulo/clase.
     * Filtra: excluye juegos ya aprobados, máximo 3 intentos desaprobados.
     */
    public function getActiveGame(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'game_for' => 'required|string|in:course,module,class',
                'id_type' => 'required|integer',
            ]);

            $userId = $request->user()->id;
            $gameFor = $request->input('game_for');
            $idType = (int) $request->input('id_type');

            $fieldId = match ($gameFor) {
                'course' => 'course_id',
                'module' => 'module_id',
                'class' => 'lesson_id',
            };

            $games = CourseGame::where([$fieldId => $idType, 'status' => 1])->get();

            if ($games->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $validGameIds = [];
            foreach ($games as $game) {
                $userGames = UserGame::where('id_course_games', $game->id)
                    ->where('id_user', $userId)
                    ->get();

                $isApproved = false;
                $disapprovedCount = 0;

                foreach ($userGames as $ug) {
                    if ($ug->condition === 'Approved') {
                        $isApproved = true;
                        break;
                    } elseif ($ug->condition === 'Disapproved') {
                        $disapprovedCount++;
                    }
                }

                if (!$isApproved && $disapprovedCount < 3) {
                    $validGameIds[] = $game->id;
                }
            }

            return response()->json(['success' => true, 'data' => $validGameIds]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error getting active game: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener juego activo'], 500);
        }
    }

    /**
     * POST /marketing/courses/game/module-active
     * Obtiene juegos activos a nivel curso + nivel módulo.
     */
    public function getActiveModuleGame(Request $request): JsonResponse
    {
        try {
            $request->validate(['id_course' => 'required|integer|exists:courses,id']);

            $userId = $request->user()->id;
            $courseId = (int) $request->input('id_course');

            // Juego a nivel curso
            $courseGame = CourseGame::select('id', 'title')
                ->where(['course_id' => $courseId, 'module_id' => null, 'lesson_id' => null, 'status' => 1])
                ->first();
            $courseGame = $this->validateUserGame($userId, $courseGame);

            // Juegos a nivel módulo
            $modules = Module::select('id')->where('id_courses', $courseId)->get();
            $moduleGames = [];
            foreach ($modules as $module) {
                $modGame = CourseGame::select('id', 'title')
                    ->where(['module_id' => $module->id, 'lesson_id' => null, 'status' => 1])
                    ->first();
                $validated = $this->validateUserGame($userId, $modGame);
                if ($validated) {
                    $moduleGames[] = $validated;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'course_game' => $courseGame ?? 'Ninguna dinámica disponible',
                    'module_games' => count($moduleGames) > 0 ? $moduleGames : 'Ninguna dinámica disponible',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting active module game: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener juegos activos por módulo'], 500);
        }
    }

    /**
     * Valida si el usuario puede acceder al juego (no aprobado y < 3 desaprobados).
     */
    private function validateUserGame(int $userId, $game): ?object
    {
        if (!$game) {
            return null;
        }

        $userGames = UserGame::where('id_course_games', $game->id)
            ->where('id_user', $userId)
            ->get();

        if ($userGames->isEmpty()) {
            return $game;
        }

        $isApproved = false;
        $disapprovedCount = 0;

        foreach ($userGames as $ug) {
            if ($ug->condition === 'Approved') {
                $isApproved = true;
                break;
            } elseif ($ug->condition === 'Disapproved') {
                $disapprovedCount++;
            }
        }

        if (!$isApproved && $disapprovedCount < 3) {
            return $game;
        }

        return null;
    }

    /**
     * POST /marketing/courses/game/add-points
     * Registra puntos ganados en un juego y lo marca como aprobado/desaprobado.
     */
    public function addPointsToUser(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'course_game_id' => 'required|integer|exists:course_games,id',
                'game_type' => 'required|string',
                'productor_id' => 'nullable|integer',
                'tiempo' => 'nullable|numeric',
                'data' => 'nullable',
                'achieved_points' => 'nullable|integer',
            ]);

            $userId = $request->user()->id;
            $courseGameId = (int) $request->input('course_game_id');

            // Si no hay data, es desaprobado
            if (!$request->has('data') || !$request->input('data')) {
                $this->saveUserGameCondition($userId, $courseGameId, 'Disapproved');
                return response()->json(['success' => true, 'data' => 0, 'message' => 'Juego desaprobado']);
            }

            // Calcular puntos según tipo de juego
            $pointsToAdd = 0;
            $gameType = $request->input('game_type');

            $pointsToAdd = match ($gameType) {
                'wordWheel' => (int) ($request->input('achieved_points', 0)),
                default => 10, // Puntos por defecto para ahorcado, cartas, etc.
            };

            DB::beginTransaction();

            try {
                // Obtener o crear user_classroom_points
                $userPoints = UserClassroomPoint::firstOrCreate(
                    ['id_user' => $userId],
                    ['total_points' => 0]
                );

                // Crear detalle de puntos
                ClassroomPointDetail::create([
                    'id_user_classroom_points' => $userPoints->id,
                    'increment_points' => $pointsToAdd,
                    'description' => 'Completar dinámica',
                    'completion_time' => $request->input('tiempo'),
                    'id_course_games' => $courseGameId,
                ]);

                // Actualizar total
                $userPoints->increment('total_points', $pointsToAdd);

                // Registrar condición
                $this->saveUserGameCondition($userId, $courseGameId, 'Approved');

                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => $pointsToAdd,
                    'message' => 'Puntos añadidos correctamente'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error adding points to user: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al añadir puntos'], 500);
        }
    }

    /**
     * Guarda la condición de un usuario en un juego.
     */
    private function saveUserGameCondition(int $userId, int $courseGameId, string $condition): void
    {
        $userGame = new UserGame();
        $userGame->id_user = $userId;
        $userGame->id_course_games = $courseGameId;
        $userGame->condition = $condition;
        $userGame->save();
    }

    /**
     * POST /marketing/courses/game/retrieve-top
     * Obtiene el top 10 de un juego específico + posición del usuario actual.
     */
    public function retrieveDynamicTop(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'course_game_id' => 'required|integer|exists:course_games,id',
            ]);

            $courseGameId = (int) $request->input('course_game_id');
            $userId = $request->user()->id;

            // Latest records per user
            $latestRecords = ClassroomPointDetail::where('id_course_games', $courseGameId)
                ->join('user_classroom_points', 'classroom_point_details.id_user_classroom_points', '=', 'user_classroom_points.id')
                ->join('users', 'users.id', '=', 'user_classroom_points.id_user')
                ->select('users.id', 'users.username', 'users.photo', DB::raw('MAX(classroom_point_details.created_at) as latest_created_at'))
                ->groupBy('users.id', 'users.username', 'users.photo');

            $topUsers = DB::table('classroom_point_details')
                ->join('user_classroom_points', 'classroom_point_details.id_user_classroom_points', '=', 'user_classroom_points.id')
                ->join('users', 'users.id', '=', 'user_classroom_points.id_user')
                ->joinSub($latestRecords, 'latest_records', function ($join) {
                    $join->on('users.id', '=', 'latest_records.id')
                        ->on('classroom_point_details.created_at', '=', 'latest_records.latest_created_at');
                })
                ->select(
                    'users.id',
                    'users.username',
                    'users.photo',
                    'classroom_point_details.increment_points',
                    'classroom_point_details.completion_time'
                )
                ->orderBy('classroom_point_details.increment_points', 'desc')
                ->orderBy('classroom_point_details.completion_time', 'asc')
                ->get();

            if ($topUsers->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => ['currentUser' => false, 'topTen' => false]
                ]);
            }

            // Encontrar posición del usuario actual
            $foundIndex = null;
            $topUsers->each(function ($item, $index) use ($userId, &$foundIndex) {
                if ((int) $item->id === $userId) {
                    $foundIndex = $index;
                    return false;
                }
            });

            $currentUserData = $foundIndex !== null
                ? array_merge((array) $topUsers->get($foundIndex), ['pos' => $foundIndex + 1])
                : false;

            return response()->json([
                'success' => true,
                'data' => [
                    'currentUser' => $currentUserData,
                    'topTen' => $topUsers->take(10)->values()->toArray(),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error retrieving dynamic top: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener ranking'], 500);
        }
    }

    // ==================== STUDENT DASHBOARD ====================

    /**
     * GET /marketing/courses/student-dashboard
     * Dashboard del estudiante: últimas lecciones, cursos relacionados,
     * cursos por preferencias y últimos cursos.
     */
    public function studentDashboard(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $sections = [];

            // 1. Últimas lecciones vistas
            $latestLessons = \App\Models\LatestLessons::where('users_id', $userId)
                ->with('class.module.course')
                ->orderBy('updated_at', 'desc')
                ->take(6)
                ->get();

            if ($latestLessons->isNotEmpty()) {
                $lessonData = $latestLessons->map(fn($ll) => [
                    'id_class' => $ll->class?->id,
                    'name_class' => $ll->class?->name,
                    'img_course' => $ll->class?->module?->course?->image,
                    'id_category' => $ll->class?->module?->course?->id_categories,
                ]);
                $sections[] = ['latest_lessons' => $lessonData];
            }

            // 2. Cursos relacionados (por compras previas)
            $purchasedCategoryIds = PurchasedCourse::where('user_id', $userId)
                ->join('courses', 'courses.id', '=', 'purchased_courses.course_id')
                ->select('courses.id_categories')
                ->distinct()
                ->pluck('id_categories')
                ->toArray();

            if (!empty($purchasedCategoryIds)) {
                $relatedCourses = Course::select('id', 'title', 'description', 'image', 'price', 'user_id')
                    ->with('user')
                    ->whereIn('id_categories', $purchasedCategoryIds)
                    ->where('status', 2)
                    ->take(6)
                    ->get();
                $sections[] = ['courses_related' => $relatedCourses];
            }

            // 3. Cursos por preferencias del usuario
            if (\Illuminate\Support\Facades\Schema::hasTable('preferences')) {
                $prefCategoryIds = \Illuminate\Support\Facades\DB::table('preferences')
                    ->where('user_id', $userId)
                    ->pluck('categories_id')
                    ->toArray();

                if (!empty($prefCategoryIds)) {
                    $prefCourses = Course::select('id', 'title', 'description', 'image', 'price', 'user_id')
                        ->with('user')
                        ->whereIn('id_categories', $prefCategoryIds)
                        ->where('status', 2)
                        ->take(6)
                        ->get();
                    $sections[] = ['courses_preferences' => $prefCourses];
                }
            }

            // 4. Lista de cursos del productor (para el menú "mis cursos")
            $producerCourses = Course::select('id', 'title')
                ->where('user_id', $userId)
                ->where('status', 2)
                ->get();
            $sections[] = ['my_courses' => $producerCourses];

            return response()->json([
                'success' => true,
                'data' => $sections
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting student dashboard: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener dashboard'], 500);
        }
    }

    // ==================== STUDENT DASHBOARD EXTRAS ====================

    /**
     * GET /marketing/courses/list/random
     * Obtiene 6 cursos aleatorios del marketplace.
     */
    public function listRandom(): JsonResponse
    {
        try {
            $courses = Course::select(
                'courses.id', 'courses.title', 'courses.description', 'courses.image',
                'courses.price', 'courses.user_id', 'courses.id_categories',
                'courses.course_level_id', 'courses.status', 'courses.marketplace_listed',
                'courses.created_at', 'courses.updated_at'
            )
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->inRandomOrder()
                ->take(6)
                ->get();

            return response()->json(['success' => true, 'data' => $courses]);
        } catch (\Exception $e) {
            Log::error('Error listing random courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar cursos aleatorios'], 500);
        }
    }

    /**
     * GET /marketing/courses/add-latest-lesson/{classId}
     * Registra la última lección vista por el usuario.
     */
    public function addLatestLesson(int $classId, Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $existing = LatestLessons::where('class_id', $classId)
                ->where('users_id', $userId)
                ->first();

            if ($existing) {
                $existing->touch(); // Actualiza updated_at
            } else {
                LatestLessons::create([
                    'users_id' => $userId,
                    'class_id' => $classId,
                ]);
            }

            // Limitar a 6 registros
            $count = LatestLessons::where('users_id', $userId)->count();
            if ($count > 6) {
                $oldest = LatestLessons::where('users_id', $userId)
                    ->orderBy('updated_at', 'ASC')
                    ->first();
                if ($oldest) {
                    $oldest->delete();
                }
            }

            return response()->json(['success' => true, 'data' => true]);
        } catch (\Exception $e) {
            Log::error('Error adding latest lesson: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al registrar lección'], 500);
        }
    }

    /**
     * GET /marketing/courses/recommended
     * Cursos recomendados basados en compras previas + preferencias + nivel de curso.
     */
    public function recommendedCourses(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $userType = $request->user()->id_account_type;
            $permittedLevels = $userType == 5 ? [1, 2] : [1, 2, 3];

            // IDs de cursos ya comprados
            $purchasedIds = PurchasedCourse::where('user_id', $userId)->pluck('course_id')->toArray();

            // Categorías de interés: por compras
            $categoryIdsFromPurchases = PurchasedCourse::where('purchased_courses.user_id', $userId)
                ->join('courses', 'courses.id', '=', 'purchased_courses.course_id')
                ->select('courses.id_categories')
                ->distinct()
                ->pluck('id_categories')
                ->toArray();

            // Categorías de interés: por preferencias
            $categoryIdsFromPrefs = [];
            if (\Illuminate\Support\Facades\Schema::hasTable('preferences')) {
                $categoryIdsFromPrefs = \Illuminate\Support\Facades\DB::table('preferences')
                    ->where('user_id', $userId)
                    ->pluck('categories_id')
                    ->toArray();
            }

            $categoryIds = count($categoryIdsFromPurchases) <= 5
                ? $categoryIdsFromPrefs
                : array_merge($categoryIdsFromPurchases, $categoryIdsFromPrefs);

            $courses = Course::join('users', 'users.id', '=', 'courses.user_id')
                ->whereIn('courses.id_categories', $categoryIds)
                ->whereIn('courses.course_level_id', $permittedLevels)
                ->whereNotIn('courses.id', $purchasedIds)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->select(
                    'courses.id', 'courses.product_type_id', 'courses.title', 'courses.slug',
                    'courses.description', 'courses.path_url', 'courses.url_portada',
                    'courses.price', 'courses.user_id', 'courses.id_categories',
                    'courses.course_level_id', 'courses.status', 'courses.marketplace_listed',
                    'courses.created_at', 'courses.updated_at', 'courses.course_about',
                    'courses.will_learn', 'courses.prev_knowledge', 'courses.course_for',
                    'users.name', 'users.last_name'
                )
                ->distinct()
                ->inRandomOrder()
                ->get();

            // Si hay ≤5 resultados, ampliar a todos los cursos (solo por nivel)
            if ($courses->count() <= 5) {
                $courses = Course::join('users', 'users.id', '=', 'courses.user_id')
                    ->whereIn('courses.course_level_id', $permittedLevels)
                    ->whereNotIn('courses.id', $purchasedIds)
                    ->where('courses.status', 2)
                    ->where('courses.marketplace_listed', 1)
                    ->select(
                        'courses.id', 'courses.product_type_id', 'courses.title', 'courses.slug',
                        'courses.description', 'courses.path_url', 'courses.url_portada',
                        'courses.price', 'courses.user_id', 'courses.id_categories',
                        'courses.course_level_id', 'courses.status', 'courses.marketplace_listed',
                        'courses.created_at', 'courses.updated_at', 'courses.course_about',
                        'courses.will_learn', 'courses.prev_knowledge', 'courses.course_for',
                        'users.name', 'users.last_name'
                    )
                    ->distinct()
                    ->inRandomOrder()
                    ->get();
            }

            return response()->json(['success' => true, 'data' => $courses]);
        } catch (\Exception $e) {
            Log::error('Error getting recommended courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener cursos recomendados'], 500);
        }
    }

    /**
     * GET /marketing/courses/available-books
     * Lista libros (product_type_id=2) disponibles en marketplace.
     */
    public function listAvailableBooks(): JsonResponse
    {
        try {
            $books = Course::select(
                'courses.id', 'courses.product_type_id', 'courses.title', 'courses.slug',
                'courses.description', 'courses.price', 'courses.url_portada',
                'courses.course_about', 'courses.will_learn', 'courses.course_for',
            )
                ->where('courses.product_type_id', 2)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->get();

            return response()->json(['success' => true, 'data' => $books]);
        } catch (\Exception $e) {
            Log::error('Error listing available books: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar libros'], 500);
        }
    }

    /**
     * GET /marketing/courses/interesting
     * Cursos interesantes basados en familias del usuario (cursos comprados).
     */
    public function interestingCourses(Request $request): JsonResponse
    {
        try {
            // Verificar que las tablas necesarias existan
            if (!\Illuminate\Support\Facades\Schema::hasTable('course_families') || !\Illuminate\Support\Facades\Schema::hasTable('families')) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $userId = $request->user()->id;

            $data = Course::join('categories', 'courses.id_categories', '=', 'categories.id')
                ->join('purchased_courses', 'courses.id', '=', 'purchased_courses.course_id')
                ->join('course_families', 'courses.id', '=', 'course_families.course_id')
                ->join('families', 'course_families.family_id', '=', 'families.id')
                ->where('purchased_courses.user_id', $userId)
                ->where('courses.user_id', '!=', $userId)
                ->select('categories.id as category_id', 'courses.id as course_id', 'families.id as family_id')
                ->get()
                ->toArray();

            if (empty($data)) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $familyIds = array_column($data, 'family_id');
            $categoryIds = array_column($data, 'category_id');
            $purchasedIds = array_column($data, 'course_id');

            $interestingCourses = Course::join('categories', 'courses.id_categories', '=', 'categories.id')
                ->join('course_level', 'courses.course_level_id', '=', 'course_level.id')
                ->join('course_families', 'courses.id', '=', 'course_families.course_id')
                ->join('families', 'course_families.family_id', '=', 'families.id')
                ->join('users', 'courses.user_id', '=', 'users.id')
                ->whereIn('families.id', $familyIds)
                ->whereIn('categories.id', $categoryIds)
                ->whereNotIn('course_families.course_id', $purchasedIds)
                ->where('courses.status', 2)
                ->where('courses.marketplace_listed', 1)
                ->select('courses.*', 'categories.name as category_name', 'course_level.description as level', 'users.name', 'users.last_name')
                ->distinct('courses.id')
                ->inRandomOrder()
                ->take(10)
                ->get();

            return response()->json(['success' => true, 'data' => $interestingCourses]);
        } catch (\Exception $e) {
            Log::error('Error getting interesting courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener cursos interesantes'], 500);
        }
    }

    /**
     * GET /marketing/courses/show-latest-lesson
     * Muestra la última lección vista por el usuario.
     */
    public function showLatestLesson(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $lessons = LatestLessons::where('users_id', $userId)
                ->with('class.module.course')
                ->orderBy('updated_at', 'DESC')
                ->get();

            if ($lessons->isEmpty()) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $data = $lessons->map(fn($ll) => [
                'id_class' => $ll->class?->id,
                'name_class' => $ll->class?->name,
                'img_course' => $ll->class?->module?->course?->image,
                'id_category' => $ll->class?->module?->course?->id_categories,
            ]);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error showing latest lesson: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al mostrar última lección'], 500);
        }
    }

    /**
     * GET /marketing/courses/producer/{producerId}
     * Lista los cursos de un productor específico.
     */
    public function listProducer(int $producerId): JsonResponse
    {
        try {
            $courses = Course::select('courses.id', 'courses.title', 'categories.name as category_name', 'courses.price', 'courses.status')
                ->join('categories', 'categories.id', '=', 'courses.id_categories')
                ->join('course_level', 'courses.course_level_id', '=', 'course_level.id')
                ->where('courses.user_id', $producerId)
                ->get();

            return response()->json(['success' => true, 'data' => $courses]);
        } catch (\Exception $e) {
            Log::error('Error listing producer courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar cursos del productor'], 500);
        }
    }

    // ==================== GAME COMMENTS ====================

    public function gameCommentsIndex(int $gameId): JsonResponse
    {
        try {
            $result = $this->listGameCommentsUseCase->execute($gameId);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error listing game comments: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar comentarios'], 500);
        }
    }

    public function gameCommentsStore(Request $request, int $gameId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string|max:1000',
            ]);

            $result = $this->createGameCommentUseCase->execute(
                $request->user()->id,
                $gameId,
                $validated['content']
            );

            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating game comment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear comentario'], 500);
        }
    }

    // ==================== CERTIFICATES ====================

    public function certificatesIndex(Request $request): JsonResponse
    {
        try {
            $certificates = $this->courseUseCase->listCertificates($request->user()->id);
            return response()->json(['success' => true, 'data' => $certificates]);
        } catch (\Exception $e) {
            Log::error('Error listing certificates: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar certificados'], 500);
        }
    }

    public function certificatesStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'course_id' => 'required|integer|exists:courses,id',
                'template_id' => 'required|integer|certificate_templates,id',
                'certificate_url' => 'nullable|string',
            ]);

            $data = array_merge($validated, ['user_id' => $request->user()->id, 'status' => 'pending']);
            $result = $this->courseUseCase->createCertificate($data);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating certificate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear certificado'], 500);
        }
    }

    public function templatesIndex(): JsonResponse
    {
        try {
            $templates = $this->courseUseCase->listTemplates();
            return response()->json(['success' => true, 'data' => $templates]);
        } catch (\Exception $e) {
            Log::error('Error listing templates: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar plantillas'], 500);
        }
    }

    public function templatesStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'template_url' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $result = $this->courseUseCase->createTemplate($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error creating template: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear plantilla'], 500);
        }
    }

    public function templatesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'template_url' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $result = $this->courseUseCase->updateTemplate($id, $validated);
            if (!$result) return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error updating template: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar plantilla'], 500);
        }
    }
}
