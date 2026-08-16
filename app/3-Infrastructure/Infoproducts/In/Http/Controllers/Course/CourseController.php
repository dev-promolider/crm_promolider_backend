<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\GetCourseDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetModuleDataUseCase;
use Promolider\Application\Courses\UseCases\Verification\GetCourseObservationsUseCase;

class CourseController extends Controller
{
    public function __construct(
        private GetCourseDataUseCase $getCourseDataUseCase,
        private GetModuleDataUseCase $getModuleDataUseCase,
        private GetCourseObservationsUseCase $getCourseObservationsUseCase
    ) {}

    public function show(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $courseData = $this->getCourseDataUseCase->execute($userId, $courseId);

        return response()->json($courseData);
    }

    public function modulesList(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $modules = $this->getModuleDataUseCase->execute($userId, $courseId);

        return response()->json($modules);
    }

    public function sendReviewRequest(Request $request, $courseId)
    {
        $course = \App\Models\Course::find($courseId);
        if (!$course) return response()->json(['data' => 'error']);

        if ((int)$course->product_type_id === 1) { // 1 = Curso
            $modules = \App\Models\Module::where('id_courses', $courseId)->get();
            if ($modules->isEmpty()) {
                return response()->json(['data' => 'empty']);
            }
            
            $hasClasses = false;
            foreach ($modules as $mod) {
                if (\App\Models\Clas::where('id_modules', $mod->id)->exists()) {
                    $hasClasses = true;
                    break;
                }
            }
            
            if (!$hasClasses) {
                return response()->json(['data' => 'empty']);
            }
        }

        if ($course->status == 1) {
            return response()->json(['data' => 'request']);
        }

        $course->status = 1;
        $course->save();

        return response()->json(['data' => 'ok']);
    }

    public function getObservations(Request $request, $courseId)
    {
        $userId = $request->user()->id;

        try {
            $observation = $this->getCourseObservationsUseCase->execute((int)$courseId, $userId);
            return response()->json(['data' => $observation], 200);
        } catch (\Exception $e) {
            $status = $e->getCode() ?: 500;
            if ($status < 100 || $status >= 600) $status = 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }
}
