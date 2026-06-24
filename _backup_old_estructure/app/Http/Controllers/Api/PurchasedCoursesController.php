<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clas;
use App\Models\PurchasedCourse;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseFormat;

class PurchasedCoursesController extends Controller
{
    use ResponseFormat;

    public function store(Request $request)
    {
        $dataset = Clas::join('modules', 'class.id_modules', '=', 'modules.id')
            ->where('modules.id_courses', '=', $request->course_id)
            ->select('class.id')
            ->get();

        $result = [];
        foreach ($dataset as $data) {
            $array_status = [$data->id, "NOT SEEN"];
            array_push($result, $array_status);
        }

        $result = json_encode($result);

        $purchased_course = new PurchasedCourse();
        $purchased_course->classes_status = $result;
        $purchased_course->course_id = $request->course_id;
        $purchased_course->user_id = auth()->user()->id;
        $purchased_course->save();

        return $this->responseOk('saved data', $purchased_course);
    }

    public function update(Request $request)
    {
        $dataset = PurchasedCourse::where('user_id', '=', auth()->user()->id)
            ->where('course_id', '=', $request->course_id)
            ->select('classes_status')
            ->get();

        $dataset = $dataset[0];
        $dataset = $dataset->classes_status;
        $dataset = json_decode($dataset, TRUE);
        $result = [];

        foreach ($dataset as $data) {
            if ($data[0] == $request->class_id) {
                $data[1] = "SEEN";
                array_push($result, $data);
            } else {
                array_push($result, $data);
            }
        }

        $purchased_course = PurchasedCourse::where('user_id', '=', auth()->user()->id)
            ->where('course_id', '=', $request->course_id)
            ->first();
        $purchased_course->classes_status = $result;

        //revision del status de todas las clases
        foreach ($dataset as $data) {
            if ($data[1] != "SEEN") {
                $purchased_course->completed_course = 1;
            }
        }
        $purchased_course->save();

        return $this->responseOk('saved data', $purchased_course);
    }

    public function show(Request $request)
    {
        \Log::info("🔍 Ejecutando show()", [
            'user_id'   => auth()->id(),
            'course_id' => $request->course_id,
        ]);
    
        $purchasedCourse = PurchasedCourse::where('user_id', auth()->id())
            ->where('course_id', $request->course_id)
            ->select('classes_status')
            ->first();
    
        if (!$purchasedCourse) {
            \Log::warning("⚠️ No se encontró purchased_course", [
                'user_id'   => auth()->id(),
                'course_id' => $request->course_id,
            ]);
        
            return response()->json([
                'error'     => 'No se encontró el curso para este usuario',
                'user_id'   => auth()->id(),
                'course_id' => $request->course_id,
            ], 404);
        }
    
        $dataset = json_decode($purchasedCourse->classes_status, true);
    
        if (!is_array($dataset)) {
            \Log::error("❌ classes_status no es un JSON válido", [
                'classes_status' => $purchasedCourse->classes_status,
            ]);
        
            return response()->json([
                'error' => 'Formato inválido en classes_status'
            ], 500);
        }
    
        $result1 = [];
        $result2 = [];
        foreach ($dataset as $data) {
            if (isset($data[0])) {
                $result1[] = $data[0];
            }
            if (isset($data[1])) {
                $result2[] = $data[1];
            }
        }
    
        \Log::info("✅ show() ejecutado correctamente", [
            'result1_count' => count($result1),
            'result2_count' => count($result2),
        ]);
    
        return $this->responseOk('', $result1, $result2);
    }

    public function saveClassSeen(Request $request)
    {
        if (!empty($request->course_id) && !empty($request->class_id)) {
            $purchased = PurchasedCourse::select('*')
                ->where('course_id', '=', $request->course_id)
                ->where('user_id', '=', auth()->user()->id)
                ->first();

            $purchased->display_time = $request->display_time;
            $purchased->last_class_reprod = $request->class_id;

            if ($purchased->classes_status == null) {
                $aux = array(
                    $request->class_id => [
                        'time' => $request->display_time,
                    ]
                );
                $purchased->classes_status = $aux;
                $purchased->update();
            } else {
                $object = json_decode($purchased->classes_status, true);
                $object[$request->class_id] = array(
                    'time' => $request->display_time,
                );
                $purchased->classes_status = $object;
                $purchased->update();
            }
            $purchased->save();

            return $this->responseOk('', $purchased);
        }
        //implementar el guardado de minutos reproducidos de una clase
        //implementar cambio de estado de la clase
    }

    public function getTime(Request $request)
    {
        $purchased_info = PurchasedCourse::where('user_id', auth()->user()->id)
            ->where('course_id', $request->courseId)
            ->pluck('classes_status');
    
        if ($purchased_info->isEmpty()) {
            return $this->responseError('Curso no comprado o no encontrado', 404);
        }
    
        $class_time = json_decode($purchased_info[0], true);
    
        if (!is_array($class_time) || !array_key_exists($request->classId, $class_time)) {
            return $this->responseOk('No hay tiempo guardado', ['time' => 0]);
        }
    
        return $this->responseOk('', $class_time[$request->classId]);
    }


    public function convertTime($time)
    {
        $t = explode(':', $time);
        return $t[0] * 3600 + $t[1] * 60 + $t[2];
    }

    public function showClassSeen(Request $request)
    {
        $idLastClassPlay = PurchasedCourse::select('last_class_reprod', 'display_time')
            ->where('course_id', '=', $request->course_id)
            ->where('user_id', '=', auth()->user()->id)
            ->first();

        if ($idLastClassPlay == null) {
            return $this->responseOk('', 'no existe');
        }
        $lastClassPlay = Clas::select('id', 'name',)->where('class.id', $idLastClassPlay->last_class_reprod)->first();
        $lastClassPlay['display_time'] = $idLastClassPlay->display_time;

        return $this->responseOk('', $lastClassPlay);
    }

    public function certificateData()
    {
        $dataset = User::select('id', 'username', 'name', 'last_name')->with('purchaseds.courses:id,title,course_time')
            ->with(['purchaseds' => function ($query) {
                $query->where('completed_course', '=', true);
            }])->find(auth()->user()->id);

        return $this->responseOk('', $dataset);
    }
}
