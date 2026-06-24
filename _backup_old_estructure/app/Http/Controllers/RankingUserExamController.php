<?php

namespace App\Http\Controllers;

use App\Models\RankingUserExam;
use Illuminate\Support\Facades\DB;

class RankingUserExamController extends Controller
{
    public static function store($exam_id, $user_id, $time, $points)
    {
        try {
            DB::beginTransaction();
            $row = new RankingUserExam();
            $row->exam_id = $exam_id;
            $row->user_id = $user_id;
            $row->time = $time;
            $row->points = $points;

            if ($row->save()) {
                return $row->id;
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
