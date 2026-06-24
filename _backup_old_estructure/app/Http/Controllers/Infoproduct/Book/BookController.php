<?php

namespace App\Http\Controllers\Infoproduct\Book;

use App\Http\Controllers\Controller;
use App\Http\Requests\Infoproduct\Book\BookObservationRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;

use App\Services\Infoproduct\Book\ReviewBookService;

class BookController extends Controller
{
    public function __construct(
        private ReviewBookService $reviewBookService
    ){}

    /**
     * Aprueba o desaprueba un libro y registra las observaciones del revisor si es que las hay.
     *
     * @param  \App\Models\Course  $course
     * @param  \App\Http\Requests\Infoproduct\Book\BookObservationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reviewBook(Course $course, BookObservationRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        try {
            $this->reviewBookService->review($course, $data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al revisar el libro: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Libro revisado correctamente.'
        ], 200);
    }
}
