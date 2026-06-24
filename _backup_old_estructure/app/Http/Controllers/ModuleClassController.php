<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Clas;
use App\Models\ClassResource;
use App\Models\Course;
use App\Models\CourseObservation;
use App\Models\Video;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ModuleClassController extends Controller
{

    public function index(Module $module)
    {
        $lessons = $module->lessons;
        return $lessons;
    }

    // Estados de un curso
    public function changeStatus(Request $request)
    {
        /**
         * 0 -> no revisado - default
         * 1 -> desaprobado
         * 2 -> aprobado
         */
        $class_id = $request->id;
        $class = Clas::where('id', $class_id)->get()->first();
        $actual_module = Module::where('id', $class->id_modules)->first();

        // disapproved
        if ($request->status == 1) {
            try {
                DB::beginTransaction();
                $class->status = 1;
                if ($class->update()) {
                    // Create observation
                    $productor_id = $this->getProductor($class_id);
                    $course_id = $this->getCourse($class_id);
                    $analyst_id = Auth::user()->id;

                    $pending_revission = Clas::where('id_modules', $actual_module->id)
                        ->whereIn('status', [0, 4])
                        ->first();
                    //check for pending revisions in the actual module
                    if ($pending_revission == null) {
                        $actual_module->status = 1;
                        $actual_module->update();
                    }
                    // creamos las observaciones sin enviarlas al usuario
                    CourseObservationController::storeObservation($class_id, $analyst_id, $productor_id, $request->observation, $course_id);
                }
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw $th;
            }
        } else {
            // approved or change status of the pending observations 
            $course_obs = CourseObservation::where('id_class', $class_id)->get()->first();
            if ($course_obs) {
                $course_obs->status = 2;
                $course_obs->update();
            }

            $class->status = 2;
            $class->update();

            $pending_revission = Clas::where('id_modules', $actual_module->id)
                ->whereIn('status', [0, 4])
                ->first();
            //check for pending revisions in the actual module
            if ($pending_revission == null) {
                $dissaproved_class = Clas::where('id_modules', $actual_module->id)
                    ->where('status', 1)->first();
                if ($dissaproved_class == null) {
                    $actual_module->status = 2;
                    $actual_module->update();
                } else {
                    $actual_module->status = 1;
                    $actual_module->update();
                }
            }
        }
    }

    public function getProductor($class_id)
    {
        //search_module_by_class
        $module_id = Clas::where('id', $class_id)->get()->first()->id_modules;
        // search_course_by_module
        $course_id = Module::where('id', $module_id)->get()->first()->id_courses;
        //search_user_by_course
        $user_id =  Course::where('id', $course_id)->get()->first()->user_id;
        return $user_id;
    }

    public function getCourse($class_id)
    {
        $module_id = Clas::where('id', $class_id)->get()->first()->id_modules;
        $course_id = Module::where('id', $module_id)->get()->first()->id_courses;
        return $course_id;
    }

    public function save(Request $request)
    {
        Log::info('=== Iniciando subida de clase ===');
        Log::info('Datos del request:', $request->all());

        // Log de archivos RAW que llegan desde el form
        Log::info('Archivos recibidos en resources:', [
            'count' => $request->hasFile('resources') ? count($request->file('resources')) : 0,
            'is_file' => $request->hasFile('resources'),
            'lista' => $request->file('resources')
        ]);

        if ($request->hasFile('resources')) {
            foreach ($request->file('resources') as $file) {
                Log::info('Archivo detectado:', [
                    'nombre' => $file->getClientOriginalName(),
                    'extension' => $file->getClientOriginalExtension(),
                    'mime' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'size_mb' => round($file->getSize() / 1024 / 1024, 2) . ' MB',
                ]);
            }
        } else {
            Log::warning('⚠️ No llegó ningún archivo en $request->resources');
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $actual_module = Module::find($request->module_id);
            $actual_course = Course::find($actual_module->id_courses);
            $course_id = $actual_course->id;

            $class = new Clas();
            $class->id_modules = $request->module_id;
            $class->name = $request->title;
            $class->slug = Str::slug($request->title);
            $class->description = $request->description;
            $class->time = $request->time;
            $class->url = '/class/example';

            if ($class->save()) {

                Log::info('Clase creada correctamente', ['class_id' => $class->id]);

                if ($request->hasFile('resources')) {
                    Log::info('Procesando recursos...');
                    ClassResourceController::storeClassResources(
                        $request->file('resources'),
                        $user->id,
                        $course_id,
                        $class->id
                    );
                }

                $response['status'] = 'ok';
                $response['classes'] = Clas::where('id_modules', $request->module_id)->get();

                if ($actual_course->status == 2) {
                    $actual_course->update(['status' => 4]);
                }
                $actual_module->update(['status' => 4]);

            } else {
                Log::error('❌ Error: no se pudo guardar la clase');
                $response['status']  = 'error';
            }

            DB::commit();

            return response()->json([
                'data' => $response,
                'message' => 'Registro exitoso'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('❌ ERROR en save(): ' . $th->getMessage(), [
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
            throw $th;
        }
    }

    public function create(Module $module)
    {
        return view('content.courses.modules.create');
    }

    public function store(Request $request, Module $module)
    {
        $lesson = new Clas;
        $lesson->name = $request->name;
        $lesson->id_modules = $module->id;
        $lesson->time = '00:00:00';
        $lesson->url = '/class/example';
        $lesson->description = 'description';
        $lesson->save();
        return $lesson;
    }

    public static function delete($id)
    {
        try {
            DB::beginTransaction();

            $class = Clas::where('id', $id)->first();
            $classes_resource = ClassResource::where('class_id', $class->id)->get();
            $class_video = Video::where('class_id', $class->id)->get();

            ClassResourceController::deleteClassResource($classes_resource);
            VideoController::deleteClassVideo($class_video);

            if ($class->delete()) {
                $response['status'] = 'ok';
            } else {
                $response['status'] = 'error';
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'data' => $response,
                'message' => 'Registro eliminado'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Ocurrio u error' . $th->getMessage()
            ], 500);
        }
    }

    public function show(Module $module, Clas $cla)
    {
        $this->verifyModule($module, $cla);
        return $cla;
    }

    public function edit(Module $module, Clas $cla)
    {
        $this->verifyModule($module, $cla);
        return view('content.courses.modules.lessons.edit', compact('module', 'cla'));
    }

    public function destroy(Module $module, Clas $cla)
    {
        $this->verifyModule($module, $cla);
        $cla->delete();
        return $cla;
    }

    public function addVideo(Request $request, Module $module, Clas $cla)
    {
        $this->verifyModule($module, $cla);
        $request->validate([
            'path' => 'required|mimes:mp4,ogx,oga,ogv,ogg,webm',
        ]);
        $path = $request->file('path')->store('courses/modules/class');

        $video = Video::make(
            ['path' => $path]
        );

        $cla->video()->save($video);
        return redirect()->back()->withSuccess('The video has been uploaded successfully.');
    }

    public function delVideo(Module $module, Clas $cla)
    {
        $this->verifyModule($module, $cla);
        Storage::delete($cla->video->path);
        $cla->video()->delete();
        return redirect()->back()->withSuccess('The video has been deleted.');
    }

    protected function verifyModule(Module $module, Clas $cla)
    {
        if ($module->id != $cla->id_modules) {
            throw new HttpException(422, 'The specified module is not the actual module of the class');
        }
    }

    public function getDetailsClass(Request $request, $courseId)
    {
        $moduleId = Module::where('id_courses', $courseId)->first()->id;

        $class_data = Clas::where('id_modules', $moduleId)
            ->get()
            ->first();

        return $class_data;
    }

    public function getClassList($id)
    {
        $classes = Clas::where('id_modules', $id)
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($class) {
                $class->has_video = Video::where('class_id', $class->id)->exists();
                return $class;
            });

        return response()->json([
            'data' => $classes,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function listObservations($course_id)
    {
        $course_observations_list =  CourseObservation::where([
            'id_course' => $course_id,
            'status' => 1
        ])->get();

        return response()->json([
            'data' => $course_observations_list,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function getClassDetails($id)
    {
        $class = Clas::findOrFail($id);
        $resources = ClassResource::where('class_id', $id)->get();
        $video = Video::where('class_id', $id)->get();

        $class->has_video = $video->isNotEmpty();

        return response()->json([
            'data' => [
                'resources' => $resources,
                'video' => $video,
            ],
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function update($id, Request $request)
    {
        $user = Auth::user();
        $module_id = Clas::where('id', $id)->get()->first()->id_modules;
        $actual_module = Module::where('id', $module_id)->get()->first();
        $actual_course = Course::where('id', $actual_module->id_courses)->get()->first();
        $course_name = Helper::formatToFolderName($actual_course->title);
        $course_id = $actual_course->id;

        try {
            DB::beginTransaction();
            $class = Clas::where('id', $id)->get()->first();
            $class->name = $request->title;
            $class->slug = Str::slug($request->title);
            $class->description = $request->description;
            $class->time = $request->time;
            // Al actualizar la clase se debe revisar
            // Si el curso esta publicado no mostrará la clase pendiente a revisión
            $class->status = '4';

            if ($class->update()) {

                $resources = $request->resources;
                $resourcesRemoved = $request->resourcesRemoved;

                if ($resourcesRemoved !== null) {
                    if (count($resourcesRemoved) > 0) {
                        $destroyResources = ClassResourceController::destroyClassResource($resourcesRemoved, $id);
                    }
                }

                if ($resources !== null) {
                    if (count($resources) > 0) {
                        $updateResources = ClassResourceController::updateClassResource($resources, $user->id, $course_id, $id);
                    }
                }

                $actual_module->status = 4;
                $actual_module->update();

                $actual_course->status = 4;
                $actual_course->update();

                $response['status'] = 'ok';
            }
            DB::commit();

            return response()->json([
                'data' => $response,
                'message' => 'Registro Actualizado'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
