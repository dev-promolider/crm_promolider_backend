<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Book;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Infoproducts\UseCases\Book\DeleteBookFileUseCase;
use Promolider\Application\Infoproducts\UseCases\Book\GetBookFilesUseCase;
use Promolider\Application\Infoproducts\UseCases\Book\GetBookPreviewUseCase;
use Promolider\Application\Infoproducts\UseCases\Book\SetBookPreviewUseCase;
use Promolider\Application\Infoproducts\UseCases\Book\StoreBookFileUseCase;
use Throwable;

class BookFileController extends BaseController
{
    public function __construct(
        private GetBookFilesUseCase $getBookFilesUseCase,
        private StoreBookFileUseCase $storeBookFileUseCase,
        private DeleteBookFileUseCase $deleteBookFileUseCase,
        private SetBookPreviewUseCase $setBookPreviewUseCase,
        private GetBookPreviewUseCase $getBookPreviewUseCase
    ) {}

    public function index(Request $request, int $courseId)
    {
        try {
            $result = $this->getBookFilesUseCase->execute($courseId, $request->user());

            return response()->json([
                'success' => true,
                'data' => $result['files'],
                'meta' => [
                    'used_size' => $result['used_size'],
                    'max_size' => $result['max_size'],
                    'max_files' => $result['max_files'],
                    'course' => $result['course'],
                ],
            ], 200);

        } catch (Throwable $th) {
            Log::error('Error listando archivos del libro', [
                'course_id' => $courseId,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function store(Request $request, int $courseId)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes subir un archivo.',
                ], 422);
            }

            $bookFile = $this->storeBookFileUseCase->execute(
                $courseId,
                $request->file('file'),
                $request->user(),
                $request->boolean('is_preview')
            );

            return response()->json([
                'success' => true,
                'data' => $bookFile,
                'message' => 'Archivo subido correctamente.',
            ], 200);

        } catch (Throwable $th) {
            Log::error('Error subiendo archivo del libro', [
                'course_id' => $courseId,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Marca o desmarca el archivo como muestra gratuita del libro.
     */
    public function togglePreview(Request $request, int $bookFileId)
    {
        try {
            $activo = $this->setBookPreviewUseCase->execute($bookFileId, $request->user());

            return response()->json([
                'success' => true,
                'is_preview' => $activo,
                'message' => $activo
                    ? 'El archivo se ofrece ahora como vista previa.'
                    : 'El libro ya no tiene vista previa.',
            ], 200);

        } catch (Throwable $th) {
            Log::error('Error marcando la vista previa del libro', [
                'book_file_id' => $bookFileId,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 422);
        }
    }

    /**
     * Muestra gratuita para la ficha de venta del marketplace.
     */
    public function preview(int $courseId)
    {
        try {
            $preview = $this->getBookPreviewUseCase->execute($courseId);

            return response()->json([
                'success' => true,
                'data' => $preview,
            ], 200);

        } catch (Throwable $th) {
            Log::error('Error obteniendo la vista previa del libro', [
                'course_id' => $courseId,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $th->getMessage(),
            ], 400);
        }
    }

    public function destroy(Request $request, int $bookFileId)
    {
        try {
            $this->deleteBookFileUseCase->execute($bookFileId, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado correctamente.',
            ], 200);

        } catch (Throwable $th) {
            Log::error('Error eliminando archivo del libro', [
                'book_file_id' => $bookFileId,
                'error' => $th->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 400);
        }
    }
}
