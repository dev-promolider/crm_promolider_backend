<?php

namespace App\Http\Controllers;

use App\Models\CourseObservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseObservationController extends Controller
{
    public static function storeObservation($class, $analyst, $productor, $observation, $course)
    {
        try {
            DB::beginTransaction();
            CourseObservation::updateOrCreate(
                ['id_class' => $class],
                [
                    'id_analyst' => $analyst,
                    'id_productor' => $productor,
                    'id_course' => $course,
                    'description' => $observation,
                    'status' => 0,
                ]
            );
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        // revision registrada = 0
        // revision activa enviada al usuario = 1
        // revision resuelta = 2
    }
}
