<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course\Module;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetEditModuleDataUseCase;
use Illuminate\Support\Facades\Log;

class GetEditModuleDataController extends Controller
{
    public function __construct(
        private GetEditModuleDataUseCase $getEditModuleDataUseCase,
    ) {}

    public function __invoke(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $editModuleData = $this->getEditModuleDataUseCase->execute($userId, $courseId);

        return response()->json($editModuleData);
    }
}
