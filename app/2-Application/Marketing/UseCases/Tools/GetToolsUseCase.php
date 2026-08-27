<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class GetToolsUseCase
{
    public function __construct(
        private ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(int $userId, ?int $courseId = null): array
    {
        $grouped = $this->toolRepository->getToolsByUser($userId, $courseId);

        // Aplanar en un solo array con campo 'type' (el frontend espera un array plano)
        $tools = [];

        foreach ($grouped['masterclasses'] ?? [] as $item) {
            $item['type'] = 'masterclass';
            $tools[] = $item;
        }
        foreach ($grouped['ebooks'] ?? [] as $item) {
            $item['type'] = 'ebook';
            $tools[] = $item;
        }
        foreach ($grouped['mini_courses'] ?? [] as $item) {
            $item['type'] = 'minicourse';
            $tools[] = $item;
        }

        // Si hay un courseId, verificamos si tiene material publicitario
        if ($courseId) {
            $materialCount = \App\Models\MarketingMaterial::where('course_id', $courseId)->count();
            if ($materialCount > 0) {
                // Obtenemos el ltimo material para tener la fecha de registro
                $lastMaterial = \App\Models\MarketingMaterial::where('course_id', $courseId)->latest()->first();
                $tools[] = [
                    'id' => $courseId, // Usamos courseId como ID para que el frontend pueda navegar
                    'type' => 'material-publicitario',
                    'title' => 'Material Publicitario del Curso',
                    'nombre' => 'Material Publicitario del Curso',
                    'category_name' => 'Promoción',
                    'created_at' => $lastMaterial->created_at,
                    'distributors_count' => '-', // No aplica directamente
                    'status' => 1, // Siempre activo si existe
                ];
            }
        }

        return $tools;
    }
}
