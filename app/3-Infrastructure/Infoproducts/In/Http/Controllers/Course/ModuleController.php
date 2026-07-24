<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetModuleDataUseCase;

class ModuleController extends Controller
{
    public function __construct(
        private GetModuleDataUseCase $getModuleDataUseCase,
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $moduleData = $this->getModuleDataUseCase->execute($userId, $courseId);

        return response()->json($moduleData);
    }
}
