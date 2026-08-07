<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Module;

use App\Models\Module;
use Exception;

class CreateModuleUseCase
{
    public function execute(int $courseId, string $name): array
    {
        if (empty($name)) {
            throw new Exception("El nombre del módulo es requerido.");
        }

        $maxOrder = Module::where('id_courses', $courseId)->max('order');
        $maxOrder = $maxOrder ?? 0;

        $module = new Module();
        $module->id_courses = $courseId;
        $module->name = $name;
        $module->status = 0; // Default status
        $module->order = $maxOrder + 1;
        $module->save();

        // Return all modules for the course as the monolith did
        $modules = Module::where('id_courses', $courseId)->orderBy('order', 'asc')->get();

        return [
            'status' => 'ok',
            'modules' => $modules->toArray()
        ];
    }
}
