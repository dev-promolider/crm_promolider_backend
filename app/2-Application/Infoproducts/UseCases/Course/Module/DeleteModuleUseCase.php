<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Module;

use App\Models\Module;
use App\Models\Clas;
use Promolider\Application\Infoproducts\UseCases\Course\Lesson\DeleteClassUseCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DeleteModuleUseCase
{
    public function __construct(
        private DeleteClassUseCase $deleteClassUseCase
    ) {}

    public function execute(int $moduleId): array
    {
        try {
            DB::beginTransaction();

            $module = Module::findOrFail($moduleId);
            
            // Delete all classes associated with this module
            $classes = Clas::where('id_modules', $moduleId)->get();
            foreach ($classes as $class) {
                $this->deleteClassUseCase->execute($class->id);
            }

            if (!$module->delete()) {
                throw new Exception("Error al eliminar el módulo");
            }

            DB::commit();

            return [
                'status' => 'ok'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting module: ' . $th->getMessage());
            throw $th;
        }
    }
}
