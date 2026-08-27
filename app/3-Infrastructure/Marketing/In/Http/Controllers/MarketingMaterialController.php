<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MarketingMaterial;
use App\Models\Course;
use Aws\S3\S3Client;
use Illuminate\Support\Str;

class MarketingMaterialController extends Controller
{
    /**
     * Get all marketing materials for a course
     */
    public function index(Request $request, $courseId)
    {
        // Add auth or ownership check if necessary
        $materials = MarketingMaterial::where('course_id', $courseId)
            ->where('status', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $materials
        ]);
    }

    /**
     * Store a text description marketing material
     */
    public function storeDescription(Request $request, $courseId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $material = MarketingMaterial::create([
            'course_id' => $courseId,
            'type' => 'description',
            'content' => $request->content,
            'status' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Descripción guardada exitosamente.',
            'data' => $material
        ]);
    }

    /**
     * Delete a marketing material
     */
    public function destroy($id)
    {
        $material = MarketingMaterial::findOrFail($id);
        
        // Soft delete or hard delete depending on requirement
        // We'll hard delete for now as per usual simple implementations, 
        // and ideally delete from S3 as well.
        if ($material->file_path) {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => config('filesystems.disks.s3.region'),
                'credentials' => [
                    'key' => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ]
            ]);
            
            try {
                $s3Client->deleteObject([
                    'Bucket' => config('filesystems.disks.s3.bucket'),
                    'Key' => $material->file_path
                ]);
            } catch (\Exception $e) {
                // Log or ignore
            }
        }
        
        $material->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Material eliminado exitosamente.'
        ]);
    }

    /**
     * Generate a presigned URL to upload a file directly to S3
     */
    public function getPresignedUrl(Request $request, $courseId)
    {
        $request->validate([
            'file_name' => 'required|string',
            'file_type' => 'required|string', // e.g. image/png or video/mp4
            'type' => 'required|in:banner,video' // our internal type
        ]);

        $extension = pathinfo($request->file_name, PATHINFO_EXTENSION) ?: 'tmp';
        // Unique file name to avoid collisions
        $s3Key = 'marketing/' . $request->type . 's/course_' . $courseId . '/' . Str::uuid() . '.' . $extension;

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ]
        ]);

        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $s3Key,
            'ACL'    => 'public-read',
            'ContentType' => $request->file_type
        ]);

        $requestS3 = $s3Client->createPresignedRequest($cmd, '+15 minutes');
        $presignedUrl = (string) $requestS3->getUri();

        return response()->json([
            'status' => 'success',
            'presigned_url' => $presignedUrl,
            's3_key' => $s3Key, // Client needs to send this back when confirming
            'file_name' => $request->file_name
        ]);
    }

    /**
     * Confirm a file was uploaded to S3 and save to database
     */
    public function confirmUpload(Request $request, $courseId)
    {
        $request->validate([
            's3_key' => 'required|string',
            'file_name' => 'required|string',
            'type' => 'required|in:banner,video'
        ]);

        // Construct public URL if needed, but saving path is better
        // The frontend can build the URL using the bucket domain
        
        $material = MarketingMaterial::create([
            'course_id' => $courseId,
            'type' => $request->type,
            'file_path' => $request->s3_key,
            'file_name' => $request->file_name,
            'status' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Material guardado exitosamente.',
            'data' => $material
        ]);
    }
}
