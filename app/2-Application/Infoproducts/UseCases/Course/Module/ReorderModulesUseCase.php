<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Module;

use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReorderModulesUseCase
{
    public function execute(array $orderedIds): array
    {
        try {
            DB::beginTransaction();

            foreach ($orderedIds as $index => $id) {
                $module = Module::find($id);
                if ($module) {
                    $module->order = $index + 1;
                    $module->save();
                }
            }

            DB::commit();

            return [
                'status' => 'ok'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error reordering modules: ' . $th->getMessage());
            throw $th;
        }
    }
}
