<?php

namespace Promolider\Application\Marketing\UseCases\Dinamicas;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ManageDinamicasUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function getAll(int $userId, ?int $courseId = null): array
    {
        try {
            return $this->dinamicaRepository->getAllByUser($userId, $courseId);
        } catch (\Throwable $th) {
            Log::error('Error al obtener dinámicas del usuario', [
                'user_id' => $userId,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);
            return [];
        }
    }

    public function getById(int $id, int $userId): ?array
    {
        try {
            return $this->dinamicaRepository->findById($id, $userId);
        } catch (\Throwable $th) {
            Log::error('Error al obtener dinámica', [
                'id' => $id,
                'error' => $th->getMessage(),
            ]);
            return null;
        }
    }

    public function store(array $data, int $userId): array
    {
        try {
            $dinamica = $this->dinamicaRepository->create([
                'user_id' => $userId,
                'course_id' => $data['course_id'],
                'category_id' => $data['category_id'] ?? null,
                'nombre' => $data['nombre'],
                'tipo_dinamica' => $data['tipo_dinamica'] ?? 'ruleta',
                'descripcion' => $data['descripcion'] ?? null,
                'slug' => Str::uuid(),
                'is_public' => $data['is_public'] ?? false,
                'is_active' => false,
                'tipo_premio' => 'Premio único',
                'estado' => 'draft',
            ]);

            return [
                'success' => true,
                'message' => 'Dinámica creada correctamente',
                'dinamica_id' => $dinamica['id'],
                'slug' => $dinamica['slug'],
            ];
        } catch (\Throwable $th) {
            Log::error('Error al crear dinámica', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);
            return [
                'success' => false,
                'message' => 'Error al crear dinámica: ' . $th->getMessage(),
            ];
        }
    }

    public function storeSpecifications(int $dinamicaId, array $data, int $userId): array
    {
        try {
            return $this->dinamicaRepository->storeSpecifications($dinamicaId, $data, $userId);
        } catch (\Throwable $th) {
            Log::error('Error al guardar especificaciones', [
                'dinamica_id' => $dinamicaId,
                'error' => $th->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Error al guardar especificaciones: ' . $th->getMessage(),
            ];
        }
    }

    public function saveTrivia(int $dinamicaId, array $data, int $userId): array
    {
        try {
            return $this->dinamicaRepository->saveTriviaConfig($dinamicaId, $data, $userId);
        } catch (\Throwable $th) {
            Log::error('Error al guardar trivia', [
                'dinamica_id' => $dinamicaId,
                'error' => $th->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Error al guardar trivia: ' . $th->getMessage(),
            ];
        }
    }

    public function toggleStatus(int $id, int $userId): array
    {
        try {
            return $this->dinamicaRepository->toggleStatus($id, $userId);
        } catch (\Throwable $th) {
            Log::error('Error al cambiar estado', [
                'dinamica_id' => $id,
                'error' => $th->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $th->getMessage(),
            ];
        }
    }

    public function delete(int $id, int $userId): array
    {
        try {
            return $this->dinamicaRepository->delete($id, $userId);
        } catch (\Throwable $th) {
            Log::error('Error al eliminar dinámica', [
                'dinamica_id' => $id,
                'error' => $th->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Error al eliminar: ' . $th->getMessage(),
            ];
        }
    }

    public function update(int $id, array $data, int $userId): array
    {
        try {
            return $this->dinamicaRepository->update($id, $data, $userId);
        } catch (\Throwable $th) {
            Log::error('Error al actualizar dinámica', [
                'dinamica_id' => $id,
                'error' => $th->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Error al actualizar: ' . $th->getMessage(),
            ];
        }
    }
}
