<?php

namespace Promolider\Application\Infoproducts\UseCases\Course\Lesson;

use App\Models\Clas;
use App\Models\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class DeleteClassUseCase
{
    public function execute(int $classId): array
    {
        try {
            DB::beginTransaction();

            $class = Clas::findOrFail($classId);
            
            // Delete associated video
            $video = Video::where('class_id', $classId)->first();
            if ($video) {
                // If we want to delete from S3:
                // Storage::disk('s3')->delete($video->path);
                // But as per plan, we are keeping S3 files safe or we can delete them.
                // For now, let's just delete the DB record.
                $video->delete();
            }

            if (!$class->delete()) {
                throw new Exception("Error al eliminar la clase");
            }

            DB::commit();

            return [
                'status' => 'ok'
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Error deleting class: ' . $th->getMessage());
            throw $th;
        }
    }
}
