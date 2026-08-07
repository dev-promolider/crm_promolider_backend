<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Lesson;

use App\Models\Clas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReorderClassesUseCase
{
    public function execute(array $orderedIds): array
    {
        try {
            DB::beginTransaction();

            foreach ($orderedIds as $index => $id) {
                $class = Clas::find($id);
                if ($class) {
                    $class->order = $index + 1;
                    $class->save();
                }
            }

            DB::commit();

            return [
                'status' => 'ok'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error reordering classes: ' . $th->getMessage());
            throw $th;
        }
    }
}
