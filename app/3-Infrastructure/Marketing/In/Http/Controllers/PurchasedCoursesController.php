<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\GetPurchasedCourseUseCase;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\StorePurchasedCourseUseCase;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\UpdateClassStatusUseCase;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\SaveClassSeenUseCase;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\GetClassTimeUseCase;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\GetLastClassPlayedUseCase;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\GetCertificateDataUseCase;

class PurchasedCoursesController extends Controller
{
    public function __construct(
        private GetPurchasedCourseUseCase $getPurchasedCourseUseCase,
        private StorePurchasedCourseUseCase $storePurchasedCourseUseCase,
        private UpdateClassStatusUseCase $updateClassStatusUseCase,
        private SaveClassSeenUseCase $saveClassSeenUseCase,
        private GetClassTimeUseCase $getClassTimeUseCase,
        private GetLastClassPlayedUseCase $getLastClassPlayedUseCase,
        private GetCertificateDataUseCase $getCertificateDataUseCase,
    ) {}

    /**
     * POST /marketing/courses/purchased
     * Crear un nuevo registro de curso comprado.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|integer|min:1',
            ]);

            $userId = $request->user()->id;
            $result = $this->storePurchasedCourseUseCase->execute($userId, (int) $data['course_id']);

            return response()->json(['message' => 'saved data', 'data' => $result], 201);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * Alias legacy POST cart/buy-course
     */
    public function buyCourse(Request $request)
    {
        return $this->store($request);
    }

    /**
     * PUT /marketing/courses/purchased/update
     * Actualizar el estado de una clase a "SEEN".
     */
    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|integer|min:1',
                'class_id' => 'required|integer|min:1',
            ]);

            $userId = $request->user()->id;
            $result = $this->updateClassStatusUseCase->execute(
                $userId,
                (int) $data['course_id'],
                (int) $data['class_id']
            );

            return response()->json(['message' => 'saved data', 'data' => $result]);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * GET /marketing/courses/purchased/show
     * Obtener el estado de las clases de un curso comprado.
     */
    public function show(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|integer|min:1',
            ]);

            $userId = $request->user()->id;
            $result = $this->getPurchasedCourseUseCase->execute($userId, (int) $data['course_id']);

            return response()->json(['message' => '', 'data' => $result]);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 404;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * PATCH /marketing/courses/purchased/save-class-seen
     * Guardar tiempo de reproducción y última clase vista.
     */
    public function saveClassSeen(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|integer|min:1',
                'class_id' => 'required|integer|min:1',
                'display_time' => 'nullable|string',
            ]);

            $userId = $request->user()->id;
            $result = $this->saveClassSeenUseCase->execute(
                $userId,
                (int) $data['course_id'],
                (int) $data['class_id'],
                $data['display_time'] ?? null
            );

            return response()->json(['message' => '', 'data' => $result]);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * GET /marketing/courses/purchased/show-class-seen
     * Obtener la última clase reproducida.
     */
    public function showClassSeen(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|integer|min:1',
            ]);

            $userId = $request->user()->id;
            $result = $this->getLastClassPlayedUseCase->execute($userId, (int) $data['course_id']);

            if ($result === null) {
                return response()->json(['message' => '', 'data' => 'no existe']);
            }

            return response()->json(['message' => '', 'data' => $result]);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * GET /marketing/courses/purchased/certificate-data
     * Obtener datos para certificados de cursos completados.
     */
    public function certificateData(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $result = $this->getCertificateDataUseCase->execute($userId);

            return response()->json(['message' => '', 'data' => $result]);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * GET /marketing/courses/purchased/get-time
     * Obtener el tiempo guardado para una clase específica.
     */
    public function getTime(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|integer|min:1',
                'class_id' => 'required|integer|min:1',
            ]);

            $userId = $request->user()->id;
            $result = $this->getClassTimeUseCase->execute(
                $userId,
                (int) $data['course_id'],
                (int) $data['class_id']
            );

            return response()->json(['message' => '', 'data' => $result]);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 404;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }
}
