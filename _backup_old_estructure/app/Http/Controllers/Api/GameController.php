<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseGame;
use App\Models\CourseGameDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('previewOwl');
    }

    public function list($id)
    {
        $dinamicas = CourseGame::join('games_types', 'course_games.game_type_id', '=', 'games_types.id')
            ->select('course_games.*', 'games_types.title as type1')
            ->where('course_games.status', 1)
            ->where('course_games.course_id', $id)->get();
        foreach ($dinamicas as $valor) {

            //DINAMICA DE CURSO
            if ($valor->course_id != null && $valor->module_id == null && $valor->lesson_id == null) {
                $valor->type2 = 1;
            }

            //DINAMICA DE MODULO
            if ($valor->course_id != null && $valor->module_id != null && $valor->lesson_id == null) {
                $valor->type2 = 2;
            }

            //DINAMICA DE CLASE
            if ($valor->course_id != null && $valor->module_id == null && $valor->lesson_id != null) {
                $valor->type2 = 3;
            }
        }

        return $dinamicas;
    }

    public function datos($id)
    {
        $datos = CourseGameDetail::where('course_game_detail.game_id', $id)->get()->first();
        return $datos;
    }
}
