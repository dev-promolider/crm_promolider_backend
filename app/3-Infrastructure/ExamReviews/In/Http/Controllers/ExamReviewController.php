<?php

namespace Promolider\Infrastructure\ExamReviews\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Promolider\Application\ExamReviews\UseCases\ListExamReviewsUseCase;
use Promolider\Application\ExamReviews\UseCases\GetExamReviewDetailsUseCase;
use Promolider\Application\ExamReviews\UseCases\UpdateExamReviewUseCase;
use Promolider\Application\ExamReviews\UseCases\ListExamReviewCoursesUseCase;
use Promolider\Application\ExamReviews\UseCases\ListExamsByCourseUseCase;

class ExamReviewController extends Controller
{
    private $listUseCase;
    private $detailsUseCase;
    private $updateUseCase;
    private $listCoursesUseCase;
    private $listCourseExamsUseCase;

    public function __construct(
        ListExamReviewsUseCase $listUseCase,
        GetExamReviewDetailsUseCase $detailsUseCase,
        UpdateExamReviewUseCase $updateUseCase,
        ListExamReviewCoursesUseCase $listCoursesUseCase,
        ListExamsByCourseUseCase $listCourseExamsUseCase
    ) {
        $this->listUseCase = $listUseCase;
        $this->detailsUseCase = $detailsUseCase;
        $this->updateUseCase = $updateUseCase;
        $this->listCoursesUseCase = $listCoursesUseCase;
        $this->listCourseExamsUseCase = $listCourseExamsUseCase;
    }

    public function list()
    {
        // Obtiene el ID del productor (usuario autenticado)
        $productorId = auth()->id();
        $userExams = $this->listUseCase->execute($productorId);

        return response()->json([
            'data' => $userExams,
            'message' => 'Data recuperada con exito',
        ], 200);
    }

    public function courses()
    {
        $user = auth()->user();
        $productorId = $user->id;
        $accountType = $user->accountType;
        $isAdmin = $accountType && $accountType->account === 'Admin';

        $courses = $this->listCoursesUseCase->execute($productorId, $isAdmin);

        return response()->json([
            'data' => $courses,
            'message' => 'Cursos recuperados con éxito',
        ], 200);
    }

    public function courseExams($courseId)
    {
        $user = auth()->user();
        $productorId = $user->id;
        $accountType = $user->accountType;
        $isAdmin = $accountType && $accountType->account === 'Admin';

        $exams = $this->listCourseExamsUseCase->execute($courseId, $productorId, $isAdmin);

        return response()->json([
            'data' => $exams,
            'message' => 'Exámenes recuperados con éxito',
        ], 200);
    }

    public function detailList($headerId)
    {
        try {
            $details = $this->detailsUseCase->execute($headerId);
            return response()->json($details, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'rate' => 'required|string',
            'exam_id' => 'required|integer', // Es el user_exam_id
        ]);

        $ratesArray = explode(',', $request->rate);

        try {
            $this->updateUseCase->execute($ratesArray, $request->exam_id);
            return response()->json(['message' => 'Examen revisado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
