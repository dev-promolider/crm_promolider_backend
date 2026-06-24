<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MiniCourseDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function view($documentId)
    {
        // Buscar el documento
        $document = MiniCourseDocument::findOrFail($documentId);
        
        // Extraer la ruta de S3
        $s3Path = $this->getS3Path($document->document);
        
        // Verificar si el archivo existe
        if (!Storage::disk('s3')->exists($s3Path)) {
            abort(404, 'Documento no encontrado');
        }
        
        // Obtener información del archivo
        try {
            $mimeType = Storage::disk('s3')->mimeType($s3Path);
            $size = Storage::disk('s3')->size($s3Path);
            $fileName = basename($document->document);
            
            // Crear respuesta streaming
            return new StreamedResponse(function() use ($s3Path) {
                $stream = Storage::disk('s3')->readStream($s3Path);
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            }, 200, [
                'Content-Type' => $mimeType ?: 'application/octet-stream',
                'Content-Length' => $size,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'public, max-age=3600',
                'Accept-Ranges' => 'bytes',
            ]);
            
        } catch (\Exception $e) {
            // Si hay error, fallback a redirección temporal
            $temporaryUrl = Storage::disk('s3')->temporaryUrl($s3Path, now()->addMinutes(30));
            return redirect($temporaryUrl);
        }
    }
    
    /**
     * Extrae la ruta de S3 de la URL completa
     */
    private function getS3Path($fullUrl)
    {
        // Si es una URL completa de S3, extraer solo la ruta
        if (str_contains($fullUrl, 'amazonaws.com')) {
            $urlParts = parse_url($fullUrl);
            return ltrim($urlParts['path'], '/');
        }
        
        // Si ya es una ruta, devolverla tal como está
        return $fullUrl;
    }
}