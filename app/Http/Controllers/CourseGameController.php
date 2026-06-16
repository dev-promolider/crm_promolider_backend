<?php

namespace App\Http\Controllers;

use App\Helpers\ParseUrl;
use App\Models\Clas;
use App\Models\ClassroomPointDetail;
use App\Models\Configuration;
use App\Models\Course;
use App\Models\CourseGame;
use App\Models\CourseGameDetail;
use App\Models\GameType;
use App\Models\Module;
use App\Models\UserClassroomPoint;
use App\Models\UserConfiguration;
use App\Models\UserGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseGameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('previewOwl');
    }

    public function index($course_id)
    {
        $user_id = auth()->user()->id;
        $game_type = GameType::all();
        $course = Course::select('id', 'title')->where('id', $course_id)->first();
        $isUserGame =  $this->isUserGame($user_id, $course->id);
        if ($isUserGame === false) {
            return view('content.miscellaneous.error');
        } else {
            return view('content.gamification.games.index', compact('course', 'game_type'));
        };
    }

    public function determineType($course_game_id)
    {
        $course_game = CourseGame::find($course_game_id);
        if ($course_game->course_id != null) {
            $type = '1';
        } elseif ($course_game->module_id != null) {
            $type = '2';
        } elseif ($course_game->lesson_id != null) {
            $type = '3';
        }
        return $type;
    }

    public function indexModule($course_id)
    {
        $user_id = auth()->user()->id;
        $game_type = GameType::all();
        $course = Course::select('id', 'title')->where('id', $course_id)->get()->first();
        $isUserGame =  $this->isUserGame($user_id, $course->id);
        if ($isUserGame === false) {
            return view('content.miscellaneous.error');
        } else {
            $modules = Course::find($course_id)->modules;
            return view('content.gamification.games.module.index', compact('course', 'game_type', 'modules'));
        };
    }

    public function indexLesson($course_id)
    {
        $user_id = auth()->user()->id;
        $game_type = GameType::all();
        $course = Course::select('id', 'title')->where('id', $course_id)->get()->first();
        $isUserGame =  $this->isUserGame($user_id, $course->id);
        if ($isUserGame === false) {
            return view('content.miscellaneous.error');
        } else {
            $modules = Course::find($course_id)->modules;
            return view('content.gamification.games.lesson.index', compact('course', 'game_type', 'modules'));
        };
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $user_id = auth()->user()->id;
            
            // Validar autorización según el tipo de juego
            switch ($request->game_for) {
                case 'course':
                    if (!$this->isUserGame($user_id, $request->course_id)) {
                        return response()->json([
                            'error' => true,
                            'message' => 'No tienes permisos para crear dinámicas en este curso'
                        ], 403);
                    }
                    break;
                    
                case 'module':
                    // Obtener course_id desde el módulo y validar
                    $module = Module::find($request->module_id);
                    if (!$module || !$this->isUserGame($user_id, $module->id_courses)) {
                        return response()->json([
                            'error' => true,
                            'message' => 'No tienes permisos para crear dinámicas en este módulo'
                        ], 403);
                    }
                    break;
                    
                case 'lesson':
                    // Obtener course_id desde la lección y validar
                    $lesson = Clas::find($request->lesson_id);
                    if (!$lesson) {
                        return response()->json([
                            'error' => true,
                            'message' => 'Lección no encontrada'
                        ], 404);
                    }
                    $module = Module::find($lesson->id_modules);
                    if (!$module || !$this->isUserGame($user_id, $module->id_courses)) {
                        return response()->json([
                            'error' => true,
                            'message' => 'No tienes permisos para crear dinámicas en esta lección'
                        ], 403);
                    }
                    break;
                    
                default:
                    return response()->json([
                        'error' => true,
                        'message' => 'Tipo de juego inválido'
                    ], 400);
            }
        
            $course_game = new CourseGame();
            
            switch ($request->game_for) {
                case 'course':
                    $course_game->course_id = $request->course_id;
                    break;
                case 'module':
                    $course_game->course_id = $request->course_id;
                    $course_game->module_id = $request->module_id;
                    break;
                case 'lesson':
                    $course_game->course_id = $request->course_id;
                    $course_game->lesson_id = $request->lesson_id;
                    break;
            }
        
            $course_game->title = $request->title;
            $course_game->game_type_id = $request->game_type_id;
            $course_game->status = 0;
            $course_game->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Dinámica creada correctamente'
            ], 200);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error al crear la dinámica'
            ], 500);
        }
    }

    public function list($course_id)
    {
        $user_id = auth()->user()->id;

        // Verificar que el usuario tiene acceso al curso
        $isUserCourse = $this->isUserGame($user_id, $course_id);
        if (!$isUserCourse) {
            return response()->json([
                'error' => true,
                'message' => 'No tienes permisos para acceder a este curso'
            ], 403);
        }

        $games = CourseGame::select('course_games.*', 'games_types.title as game_type')
            ->where('course_id', $course_id)
            ->join('games_types', 'course_games.game_type_id', '=', 'games_types.id')
            ->get();

        return response()->json([
            'data' => $games,
            'message' => 'Data recuperada con éxito'
        ], 200);
    }

    public function moduleList($module_id)
    {
        $user_id = auth()->user()->id;

        // Obtener el course_id desde el módulo
        $module = Module::find($module_id);
        if (!$module) {
            return response()->json([
                'error' => true,
                'message' => 'Módulo no encontrado'
            ], 404);
        }

        // Verificar que el usuario tiene acceso al curso
        $isUserCourse = $this->isUserGame($user_id, $module->id_courses);
        if (!$isUserCourse) {
            return response()->json([
                'error' => true,
                'message' => 'No tienes permisos para acceder a este módulo'
            ], 403);
        }

        $games = CourseGame::select('course_games.*', 'games_types.title as game_type')
            ->where('module_id', $module_id)
            ->join('games_types', 'course_games.game_type_id', '=', 'games_types.id')
            ->get();

        return response()->json([
            'data' => $games,
            'message' => 'Data recuperada con éxito'
        ], 200);
    }

    public function lessonList($lesson_id)
    {
        $user_id = auth()->user()->id;

        // Obtener el course_id desde la lección
        $lesson = Clas::find($lesson_id);
        if (!$lesson) {
            return response()->json([
                'error' => true,
                'message' => 'Lección no encontrada'
            ], 404);
        }

        $module = Module::find($lesson->id_modules);
        if (!$module) {
            return response()->json([
                'error' => true,
                'message' => 'Módulo asociado no encontrado'
            ], 404);
        }

        // Verificar que el usuario tiene acceso al curso
        $isUserCourse = $this->isUserGame($user_id, $module->id_courses);
        if (!$isUserCourse) {
            return response()->json([
                'error' => true,
                'message' => 'No tienes permisos para acceder a esta lección'
            ], 403);
        }

        $games = CourseGame::select('course_games.*', 'games_types.title as game_type')
            ->where('lesson_id', $lesson_id)
            ->join('games_types', 'course_games.game_type_id', '=', 'games_types.id')
            ->get();

        return response()->json([
            'data' => $games,
            'message' => 'Data recuperada con éxito'
        ], 200);
    }

    // public function moduleEdit($game_id)
    // {
    //     $user_id = auth()->user()->id;
    //     $game = CourseGame::where('id', $game_id)->get()->first();
    //     $course_id = $game->course_id;
    //     if (!is_null($game)) {
    //         $module_game_id = $game->module_id;
    //         $course_id =  Module::where('id', $module_game_id)->get()->first()->id_courses;
    //         $detail = CourseGameDetail::where('game_id', $game->id)->get()->first();
    //         $isUserGame = $this->isUserGame($user_id, $course_id);
    //         return $this->getViewResponse($isUserGame, $game, $detail);
    //     } else {
    //         return view('content.miscellaneous.error');
    //     }
    // }

    // public function lessonEdit($game_id)
    // {
    //     $user_id = auth()->user()->id;
    //     $game = CourseGame::where('id', $game_id)->get()->first();
    //     if (!is_null($game)) {
    //         $lesson_game_id = $game->lesson_id;
    //         $module_id =  Clas::where('id', $lesson_game_id)->get()->first()->id_modules;
    //         $course_id =  Module::where('id', $module_id)->get()->first()->id_courses;
    //         $detail = CourseGameDetail::where('game_id', $game->id)->get()->first();
    //         $isUserGame = $this->isUserGame($user_id, $course_id);
    //         return $this->getViewResponse($isUserGame, $game, $detail);
    //     } else {
    //         return view('content.miscellaneous.error');
    //     }
    // }

    /**
     * Enable the game selected and disabled the others excluding the selected one
     */
    public function activate(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $user_id = auth()->user()->id;
            $current_game = CourseGame::find($request->id);
            
            if (!$current_game) {
                return response()->json([
                    'error' => true,
                    'message' => 'Juego no encontrado'
                ], 404);
            }
            
            // Obtener course_id y validar permisos
            $course_id = $this->getCourseId($current_game);
            $isUserGame = $this->isUserGame($user_id, $course_id);
            
            if (!$isUserGame) {
                return response()->json([
                    'error' => true,
                    'message' => 'No tienes permisos para activar este juego'
                ], 403);
            }
            
            if ($this->isReadyToActivate($request->id)) {
                // Cambiar estado del juego actual
                if ($current_game->status === 1) {
                    $current_game->status = 0;
                } else {
                    $current_game->status = 1;
                }
                
                if ($current_game->save()) {
                    // Desactivar otros juegos del mismo tipo
                    if (!empty($request->lesson_id)) {
                        $games = CourseGame::where('game_type_id', $request->game_type_id)
                            ->where('lesson_id', $request->lesson_id)
                            ->whereNotIn('id', array($request->id))
                            ->get();
                    } else if (!empty($request->module_id)) {
                        $games = CourseGame::where('game_type_id', $request->game_type_id)
                            ->where('module_id', $request->module_id)
                            ->whereNotIn('id', array($request->id))
                            ->get();
                    } else if (!empty($request->course_id)) {
                        $games = CourseGame::where('game_type_id', $request->game_type_id)
                            ->where('course_id', $request->course_id)
                            ->whereNotIn('id', array($request->id))
                            ->get();
                    } else {
                        throw new \Exception('Game not assigned to course');
                    }
                
                    foreach ($games as $game) {
                        $game->status = 0;
                        $game->update();
                    }
                }
                $message = 'success';
            } else {
                $message = 'failed';
            }
            
            DB::commit();
            return response()->json([
                'success' => $message === 'success',
                'message' => $message === 'success' ? 'Juego activado correctamente' : 'Configure el juego antes de activarlo'
            ], 200);
            
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Error al activar el juego'
            ], 500);
        }
    }

    public function isReadyToActivate($game_id)
    {

        return CourseGameDetail::where('game_id', $game_id)->exists();
        // $detail = CourseGameDetail::where('game_id', $game_id)->get();
        // if (count($detail) > 0) {
        //     return true;
        // } else {
        //     return false;
        // }
    }

    public function edit($game_id)
    {
        $user_id = auth()->user()->id;
        $game = CourseGame::find($game_id);

        if (!$game) {
            return view('content.miscellaneous.error');
        }

        $course_id = $this->getCourseId($game);

        // Verificar autorización
        $isUserGame = $this->isUserGame($user_id, $course_id);
        if (!$isUserGame) {
            return view('content.miscellaneous.error');
        }

        $detail = CourseGameDetail::where('game_id', $game->id)->first();
        return $this->getViewResponse($isUserGame, $game, $detail, $course_id);
    }

    public function getCourseId($game)
    {
        if ($game->course_id != null) {
            $course_id = $game->course_id;
        } elseif ($game->module_id != null) {
            $course_id = Module::where('id', $game->module_id)->get()->first()->id_courses;
        } elseif ($game->lesson_id != null) {
            $module_id = Clas::where('id', $game->lesson_id)->get()->first()->id_modules;
            $course_id = Module::where('id', $module_id)->get()->first()->id_courses;
        }
        return $course_id;
    }
    public function isUserGame($user_id, $course_id)
    {
        $user_courses = Course::where('user_id', $user_id)->pluck('id')->toArray(); //id productor
        $isUserGame = in_array($course_id, $user_courses);
        if ($isUserGame === false) {
            return false;
        } else {
            return true;
        }
    }

    public function getViewResponse($isUserGame, $game, $detail, $course_id)
    {
        if ($isUserGame === true) {
            $type = $this->determineType($game->id);
            $view = $this->getGameView($game->game_type_id);
            return view($view, compact('game', 'detail', 'type', 'course_id'));
        } else {
            return view('content.miscellaneous.error');
        }
    }

    public function getGameView($game_type)
    {
        switch ($game_type) {
                # Ahorcado
            case 1:
                $view = 'content.courses.game.editGame1';
                break;
                # Pares de cartas
            case 2:
                $view = 'content.courses.game.editGame2';
                break;
            case 3:
                $view = 'content.courses.game.editGame3';
                break;
            case 4:
                $view = 'content.courses.game.editGame4';
                break;
            case 5:
                $view = 'content.courses.game.editGame5';
                break;
            case 6:
                $view = 'content.courses.game.editGame6';
                break;
            default:
                $view = 'Error de datos';
                break;
        }
        return $view;
    }

    public function getActiveGame(Request $request)
    {
        $user = auth()->user();
        $game_for = $request->game_for;
        $id_type = $request->id_type;

        switch ($game_for) {
            case "course":
                $field_id = 'course_id';
                break;
            case "module":
                $field_id = 'module_id';
                break;
            case "class":
                $field_id = 'lesson_id';
                break;
            default:
                echo "Error";
        }

        $game =  CourseGame::where([$field_id => $id_type,  'status' => 1])->get();
        if ($game) {
            $game =  $game->pluck('id')->toArray();
            $games = [];
            //validar si el usuario tiene menos de 3 intentos desaprobados o si ya aprobo el juego
            foreach ($game as $id_course_game) {
                $userGames = UserGame::where(['id_course_games' => $id_course_game, 'id_user' => $user->id])->get();

                $valueApproved = false;
                $countDisapproved = 0;

                foreach ($userGames as $userG) {
                    if ($userG->condition == 'Approved') {
                        $valueApproved = true;
                        break;
                    } else if ($userG->condition == 'Disapproved') {
                        $countDisapproved++;
                    }
                }
                if (!$valueApproved && ($countDisapproved < 3)) {
                    array_push($games, $id_course_game);
                }
            }
            return $games;
        } else {
            return false;
        }
    }

    /**
     * return data of a game by id
     */
    public function game(Request $request)
    {
        $game_query = CourseGame::select('course_games.id', 'games_types.title as game_type', 'course_games.title')->where(['course_games.id' => $request->game_id, 'status' => 1])->join('games_types', 'course_games.game_type_id', '=', 'games_types.id')->get();

        if (count($game_query) == 0) {
            $data = 'No hay datos';
        } else {
            $game = $game_query->first();
            $detail_query = CourseGameDetail::select('data')->where('game_id', $game->id)->get();

            if (count($detail_query) == 0) {
                $data = 'No hay datos';
            } else {
                $game = $game_query->first();
                $detail = $detail_query->first();
                $detail_json[] = $this->getDataByGameType($game->game_type, $detail);
                $data = array(
                    "game" => $game,
                    "detail" => $detail_json[0]
                );
            }
        }

        return $data;
    }

    public function getDataByGameType($type, $detail)
    {
        switch ($type) {
            case 'Ahorcado':
                $detail_formated = array(
                    'word'        => $detail->data['word'],
                    'description'     => $detail->data['description'],
                );
                break;
            case 'Pares de cartas':
                $items = [];
                foreach ($detail->data as $item) {
                    $item = array(
                        'img' => ParseUrl::contacAtrrS3($item['img']),
                        'name' => $item['name']
                    );
                    array_push($items, $item);
                }
                $detail_formated = $items;
                break;

            case 'Búho':
                $questions = [];
                foreach ($detail->data as $question) {
                    $question = array(
                        'question' => $question['question'],
                        'alternative1' => $question['alternative1'],
                        'alternative2' => $question['alternative2'],
                        'alternative3' => $question['alternative3'],
                        'alternative4' => $question['alternative4'],
                        'answer' => $question['answer']
                    );
                    array_push($questions, $question);
                }
                $detail_formated = $questions;
                break;
            default:
                $detail_formated = 'Error de datos';
                break;
        }

        return $detail_formated;
    }

    /**
     * $game_type
     * $productor_id
     */
    public function addPointsToUser(Request $request)
    {
        if (!$request->data) {
            //false
            //condition Approved or Disapproved
            $this->userGameCondition(auth()->user()->id, $request->course_game_id, 'Disapproved');
            return 0;
        }
        switch ($request->game_type) {
            case 'ahorcado':
                $id_conf_dynamic = Configuration::where('option', 'dynamics_1')->get()->first()->id;
                $points_to_add = UserConfiguration::where([
                    'configuration_id' => $id_conf_dynamic,
                    'user_id' => $request->productor_id
                ])->get()->first()->value;
                break;
            case 'cartas':
                $id_conf_dynamic = Configuration::where('option', 'dynamics_2')->get()->first()->id;
                $points_to_add = UserConfiguration::where([
                    'configuration_id' => $id_conf_dynamic,
                    'user_id' => $request->productor_id
                ])->get()->first()->value;
                break;
            case 'wordWheel':
                $points_to_add = $request->achieved_points;
                break;
            default:
                return 'Error';
                break;
        }

        try {
            DB::beginTransaction();
            $user_points_id = UserClassroomPoint::where('id_user', auth()->user()->id)->get()->first()->id;
            $user_point_detail = new ClassroomPointDetail();
            $user_point_detail->id_user_classroom_points = $user_points_id;
            $user_point_detail->increment_points = $points_to_add;
            $user_point_detail->description = "Completar dinámica";
            $user_point_detail->completion_time = $request->tiempo;
            $user_point_detail->id_course_games = $request->course_game_id;
            $user_point_detail->save();
            $user_points = UserClassroomPoint::where('id_user', auth()->user()->id)->get()->first();
            $user_points->total_points = $user_points->total_points + $points_to_add;
            $user_points->update();
            $this->userGameCondition(auth()->user()->id, $request->course_game_id, 'Approved');
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return $points_to_add;
    }

    public function retrieveDynamicTop(Request $request)
    {

        $latestRecords = ClassroomPointDetail::where('id_course_games', $request->course_game_id)
            ->join('user_classroom_points', 'classroom_point_details.id_user_classroom_points', '=', 'user_classroom_points.id')
            ->join('users', 'users.id', '=', 'user_classroom_points.id_user')
            ->select('users.username', DB::raw('MAX(classroom_point_details.created_at) as latest_created_at'))
            ->groupBy('users.username');

        $dynamicTopTen = DB::table('classroom_point_details')
            ->join('user_classroom_points', 'classroom_point_details.id_user_classroom_points', '=', 'user_classroom_points.id')
            ->join('users', 'users.id', '=', 'user_classroom_points.id_user')
            ->joinSub($latestRecords, 'latest_records', function ($join) {
                $join->on('users.username', '=', 'latest_records.username')
                    ->on('classroom_point_details.created_at', '=', 'latest_records.latest_created_at');
            })
            ->select('users.id', 'users.username', 'users.photo', 'latest_records.latest_created_at', 'classroom_point_details.increment_points', 'classroom_point_details.completion_time')

            ->orderBy('classroom_point_details.increment_points', 'desc')->orderBy('classroom_point_details.completion_time', 'asc')
            ->get();

        if ($dynamicTopTen->isEmpty()) {
            $dynamicRanking = ['currentUser' => false, 'topTen' => false];
        } else {

            $targetUserId = $request->user()->id;

            $foundIndex = null;
            $dynamicTopTen->each(function ($item, $index) use ($targetUserId, &$foundIndex) {
                if ($item->id == $targetUserId) {
                    $foundIndex = $index;
                    return false;
                }
            });
            if ($foundIndex === null) {
                $currentUserRankData = false;
            } else {
                $currentUserRankData = (array) $dynamicTopTen->get($foundIndex);
                $currentUserRankData['pos'] = $foundIndex + 1;
            }
            $dynamicRanking = ['currentUser' => $currentUserRankData, 'topTen' => $dynamicTopTen->take(10)->toArray()];
        }
        return $dynamicRanking;
    }

    public function allDynamicsTop($id, Request $request)
    {
        $latestGameRecords = CourseGame::where('course_id', $id)->where('status', 1)
            ->join('classroom_point_details', 'course_games.id', '=', 'classroom_point_details.id_course_games')
            ->join('user_classroom_points', 'classroom_point_details.id_user_classroom_points', '=', 'user_classroom_points.id')
            ->join('users', 'users.id', '=', 'user_classroom_points.id_user')
            ->select('users.username', DB::raw('MAX(classroom_point_details.created_at) as latest_created_at'))
            ->groupBy('users.username')->groupBy('classroom_point_details.id_course_games');


        $allResults = DB::table('classroom_point_details')
            ->join('user_classroom_points', 'classroom_point_details.id_user_classroom_points', '=', 'user_classroom_points.id')
            ->join('users', 'users.id', '=', 'user_classroom_points.id_user')
            ->joinSub(
                $latestGameRecords,
                'latest_records',
                function ($join) {
                    $join->on('users.username', '=', 'latest_records.username')
                        ->on('classroom_point_details.created_at', '=', 'latest_records.latest_created_at');
                }
            )
            ->select('users.id', 'users.username', 'users.photo', DB::raw('MAX(latest_records.latest_created_at) as latest_record'), DB::raw('SUM(classroom_point_details.increment_points) as total_points'), DB::raw('AVG(classroom_point_details.completion_time) as avg_time'))
            ->groupBy('users.username', 'users.id', 'users.photo')
            ->orderBy('total_points', 'desc')->orderBy('avg_time', 'asc')
            ->get();

        if ($allResults->isEmpty()) {
            $allRankingData = ['currentUser' => false, 'topTen' => false];
        } else {

            $targetUserId = $request->user()->id;
            $foundIndex = null;
            $allResults->each(function ($item, $index) use ($targetUserId, &$foundIndex) {
                if ($item->id == $targetUserId) {
                    $foundIndex = $index;
                    return false;
                }
            });
            if ($foundIndex === null) {
                $currentUserRankData = false;
            } else {
                $currentUserRankData = (array) $allResults->get($foundIndex);
                $currentUserRankData['pos'] = $foundIndex + 1;
            }
            $allRankingData = ['currentUser' => $currentUserRankData, 'topTen' => $allResults->take(10)->toArray()];
        }
        return $allRankingData;;
    }


    public function userGameCondition($id_user, $id_course_games, $condition)
    {
        $userGame = new UserGame();
        $userGame->id_user = $id_user;
        $userGame->id_course_games = $id_course_games;
        $userGame->condition = $condition;
        $userGame->save();
    }
    public function getActiveModuleGame(Request $request)
    {

        $moduleGames = array();
        //GAME COURSE (object)
        $courseGame = CourseGame::select('id', 'title')->where([
            'course_id' => $request->id_course,
            'module_id' => null,
            'lesson_id' => null,
            'status' => '1'
        ])->get()->first();
        $courseGame = $this->validateGameModule(auth()->user()->id, $courseGame);

        //GAME MODULE (array)
        $modules = Module::select('id', 'id_courses')->where(['id_courses' => $request->id_course])->get();
        foreach ($modules as $module) {

            $courseModuleGame = CourseGame::select('id', 'title')->where([
                'module_id' => $module->id,
                'lesson_id' => null,
                'status' => '1'
            ])->get()->first();
            //la funcion retorna el juego si cumple con las restricciones
            $moduleGame = $this->validateGameModule(auth()->user()->id, $courseModuleGame);
            //el juego se añade al arreglo si la funcion retorna el juego
            if ($moduleGame) {
                array_push($moduleGames, $moduleGame);
            }
        }

        if ($courseGame == null) {
            $courseGame = "Ninguna dinámica disponible";
        }
        if (count($moduleGames) == 0) {
            $moduleGames = "Ninguna dinámica disponible";
        }

        $gameCourseModule = (object) array('course_game' => $courseGame, 'module_games' => $moduleGames);
        return $gameCourseModule;
    }

    //funcion para validar si el juego ya esta registrado
    public function validateGameModule($user_id, $game)
    {
        if ($game) {
            $userGames = UserGame::where(['id_course_games' => $game->id])
                ->where('id_user', $user_id)->get();
            if (count($userGames) > 0) {

                $valueApproved = false;
                $countDisapproved = 0;

                foreach ($userGames as $userG) {
                    if ($userG->condition == 'Approved') {
                        $valueApproved = true;
                        break;
                    } else if ($userG->condition == 'Disapproved') {
                        $countDisapproved++;
                    }
                }
                if (!$valueApproved && ($countDisapproved < 3)) {
                    return $game;
                }
            } else {
                return $game;
            }
        } else {
            return;
        }
    }

    // public function previewOwl($id){
    //     $game = CourseGameDetail::where('game_id', $id)
    //         ->select('data')
    //         ->first();
    //     return view('content.courses.game.previewGame', compact('game'));
    // }

    public function previewGame($id)
    {
        $game_query = CourseGame::select('course_games.id', 'games_types.title as game_type', 'course_games.title')
            ->where('course_games.id', $id)
            ->join('games_types', 'course_games.game_type_id', '=', 'games_types.id')
            ->get();

        if (count($game_query) == 0) {
            $data = 'No hay datos';
        } else {
            $game = $game_query->first();
            $detail_query = CourseGameDetail::select('data')->where('game_id', $game->id)->get();

            if (count($detail_query) == 0) {
                $data = 'No hay datos';
            } else {
                $detail = $detail_query->first();
                $detail_json[] = $this->getDataByGameType($game->game_type, $detail);
                $data = array(
                    "game" => $game,
                    "detail" => $detail_json[0]
                );
            }
        }

        return view('content.courses.game.previewGame', compact('data'));
    }
}
