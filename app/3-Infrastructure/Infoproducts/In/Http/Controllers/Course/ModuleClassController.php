<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\Lesson\GetModuleClassDataUseCase;

class ModuleClassController extends Controller
{
    public function __construct(
        private GetModuleClassDataUseCase $getModuleClassDataUseCase,
    ) {}

    public function getClassList(Request $request)
    {
        $moduleId = (int) $request->route('moduleId');

        $lessonData = $this->getModuleClassDataUseCase->execute($moduleId);

        return response()->json($lessonData, 200);
    }
}
