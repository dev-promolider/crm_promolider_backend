<?php

namespace App\Services\Infoproduct\Book;

use App\Models\Infoproduct\Book\BookFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreBookFileService
{
    public function store($file, $course_id, $user_id)
    {
        // Obteniendo los MB acumulados en archivos del libro
        $totalSize = BookFile::where('course_id', $course_id)->sum('size');
        $fileSize = $file->getSize();

        if ($totalSize + $fileSize > 1024 * 1024 * 250) { // 250 MB
            throw new \InvalidArgumentException("El tamaño total de los archivos excede el límite permitido.");
        }

        DB::beginTransaction();

        try {
            $extension = $file->extension();
            $filename = $file->getClientOriginalName();

            $unique_id = Str::uuid()->toString();

            $path = "books/{$user_id}/{$course_id}/{$unique_id}";

            // Guardando archivo en el storage
            Storage::disk('public')->putFileAs($path, $file, $filename);
            
            // Guardando en la BD
            $bookFile = BookFile::create([
                'course_id' => $course_id,
                'file_type' => $extension,
                'file_name' => $filename,
                'file_path' => "{$path}/{$filename}",
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}