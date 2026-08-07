<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Module;

use App\Models\Module;
use Exception;
use Illuminate\Support\Facades\Log;

class UpdateModuleUseCase
{
    public function execute(int $moduleId, string $name): array
    {
        try {
            $module = Module::findOrFail($moduleId);
            $module->name = $name;
            
            if (!$module->save()) {
                throw new Exception("Error al actualizar el módulo");
            }

            return [
                'status' => 'ok',
                'module' => $module->toArray()
            ];

        } catch (\Throwable $th) {
            Log::error('Error updating module: ' . $th->getMessage());
            throw $th;
        }
    }
}
