<?php

namespace App\Http\Controllers;

use App\Http\Requests\ModuleRequest;
use App\Models\Clas;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CourseModuleController extends Controller
{
    
    public function index(Course $course)
    {
        $modules = $course->modules;
        return $modules;
    }

    public function create(Course $course)
    {
        $this->authorize('update', $course);
        return view('content.courses.modules.create', compact('course'));
    }

    public function store(ModuleRequest $request)
    {
        try {
            DB::beginTransaction();

            $maxOrder = Module::where('id_courses', $request->course_id)
                    ->max('order');

            $maxOrder = $maxOrder ?? 0;

            $module = new Module();
            $module->id_courses = $request->course_id;
            $module->name = $request->name;
            $module->status = 0;
            $module->order = $maxOrder + 1;
            if ($module->save()) {
                $response['status'] = 'ok';
                $response['modules'] = Module::where('id_courses', $request->course_id)->get();
            } else {
                $response['status']  = 'error';
            }

            DB::commit();

            return response()->json([
                'data' => $response,
                'message' => 'Registro exitoso',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function show(Course $course, Module $module)
    {
        $this->verifyCourse($course, $module);
        return $module;
    }

    public function edit(Course $course, Module $module)
    {
        //
    }

    public function update($id, Request $request)
    {
        $module = Module::where('id', $id);
        $module->update([
            'name' => $request->name
        ]);
        
        return response()->json([
            'data' => $request->name,
            'message' => 'Data recuperada con exito',
        ], 200);
    }


    public static function delete($id)
    {
        $course_id = Module::where('id', $id)->first()->id_courses;
        try {
            DB::beginTransaction();
            $module = Module::where('id', $id)->first();
            $classes = Clas::where('id_modules', $module->id)->get();

            if (count($classes)) {
                foreach ($classes as $class) {
                    ModuleClassController::delete($class->id);
                }
            }

            if ($module->delete()) {
                $res['modules'] =  Module::where('id_courses', $course_id)->get();
                $res['status'] = 'ok';
            } else {
                $res = 'error';
            }
            
            DB::commit();
            return response()->json([
                'data' => $res,
                'message' => 'Registro eliminado'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    protected function verifyCourse(Course $course, Module $module)
    {
        if ($course->id != $module->id_courses) {
            throw new HttpException(422, 'The specified course is not the actual course of the modules');
        }
    }

    public function createModule(Request $request)
    {
        return view('content.courses.modules.create');
    }

    // Show the view to manage the modules of specific course
    public function editModule($id)
    {
        $course = Course::where('id', $id)->get()->first();
        $modules = Module::where('id_courses', $id)->orderBy('order','asc')->get();
        return view('content.courses.modules.edit', compact('course', 'modules'));
    }
}
