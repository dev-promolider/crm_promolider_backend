<?php

namespace Promolider\Infrastructure\Courses\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Promolider\Application\Courses\UseCases\Verification\ListPendingCoursesUseCase;
use Promolider\Application\Courses\UseCases\Verification\ApproveCourseUseCase;
use Promolider\Application\Courses\UseCases\Verification\RejectCourseUseCase;

class CourseVerificationController extends Controller
{
    private $listUseCase;
    private $approveUseCase;
    private $rejectUseCase;

    public function __construct(
        ListPendingCoursesUseCase $listUseCase,
        ApproveCourseUseCase $approveUseCase,
        RejectCourseUseCase $rejectUseCase
    ) {
        $this->listUseCase = $listUseCase;
        $this->approveUseCase = $approveUseCase;
        $this->rejectUseCase = $rejectUseCase;
        // The policy should be enforced here or in routes middleware
    }

    public function index()
    {
        $courses = $this->listUseCase->execute();
        return response()->json([
            'data' => $courses,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function approve($id)
    {
        try {
            $this->approveUseCase->execute($id);
            return response()->json(['message' => 'Infoproducto aprobado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al aprobar el infoproducto', 'details' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'observation' => 'nullable|string'
        ]);

        try {
            $this->rejectUseCase->execute($id, $request->input('observation'), auth()->id());
            return response()->json(['message' => 'Observaciones enviadas con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al rechazar el infoproducto', 'details' => $e->getMessage()], 500);
        }
    }
}
