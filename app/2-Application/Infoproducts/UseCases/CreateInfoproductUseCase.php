<?php

namespace Promolider\Application\Infoproducts\UseCases;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Exception;

class CreateInfoproductUseCase
{
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(array $data, User $user, $coverFile = null, $promoFile = null): array
    {
        // Validation of product_type_id
        if (!isset($data['product_type_id']) || !in_array($data['product_type_id'], ['1', '2'])) {
            throw new Exception('Valor de product_type_id no válido o requerido.');
        }

        // Common validations for both Course and Book
        $requiredFields = ['title', 'description', 'price_base', 'price', 'course_about', 'will_learn', 'prev_knowledge', 'course_for'];
        foreach ($requiredFields as $field) {
            // Check array_key_exists instead of isset to allow empty strings if necessary
            // Or better, check if the key exists and is not null since ConvertEmptyStringsToNull middleware is active.
            if (!array_key_exists($field, $data)) {
                throw new Exception("Faltan campos requeridos: {$field}");
            }
        }

        if ($data['product_type_id'] === '1') {
            if (!isset($data['id_categories']) || !isset($data['course_level_id']) || !isset($data['months']) || !isset($data['certificate'])) {
                throw new Exception('Faltan campos requeridos para curso (categoría, nivel, meses, certificado)');
            }
        }

        $nextId = $this->infoproductRepository->getNextId();
        
        $infoproductData = [
            'user_id' => $user->id,
            'product_type_id' => $data['product_type_id'],
            'id_categories' => $data['product_type_id'] === '1' ? $data['id_categories'] : 8,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'description' => $data['description'],
            'price_base' => $data['price_base'],
            'price' => $data['price'],
            'course_level_id' => $data['product_type_id'] === '1' ? $data['course_level_id'] : null,
            'course_about' => $data['course_about'],
            'will_learn' => $data['will_learn'],
            'prev_knowledge' => $data['prev_knowledge'],
            'course_for' => $data['course_for'],
            'currency' => 'soles',
            'months' => $data['product_type_id'] === '1' ? $data['months'] : null,
            'certificate' => isset($data['certificate']) && ($data['certificate'] === 'true' || $data['certificate'] == 1) ? 1 : 0,
            'status' => 0,
            'marketplace_listed' => 1
        ];

        // Initialize S3 Client using config instead of env directly
        $s3Client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
        $bucket = config('filesystems.disks.s3.bucket');

        // Process files
        if ($coverFile) {
            $portadaFilename = $this->formatFilename($coverFile->getClientOriginalName());
            $portadaPath = 'courses/' . $user->id . '/' . $nextId . '/cover/';
            
            $uploader = new \Aws\S3\MultipartUploader($s3Client, $coverFile->getRealPath(), [
                'bucket' => $bucket,
                'key' => $portadaPath . $portadaFilename,
                'ACL' => 'public-read',
            ]);
            $uploader->upload();

            $infoproductData['portada'] = $portadaFilename;
            $infoproductData['url_portada'] = $portadaPath . $portadaFilename;
        }

        if ($promoFile) {
            $promoFilename = $this->formatFilename($promoFile->getClientOriginalName());
            $promoPath = 'courses/' . $user->id . '/' . $nextId . '/promo/';
            
            $uploader = new \Aws\S3\MultipartUploader($s3Client, $promoFile->getRealPath(), [
                'bucket' => $bucket,
                'key' => $promoPath . $promoFilename,
                'ACL' => 'public-read',
            ]);
            $uploader->upload();

            $infoproductData['path_url'] = $promoPath . $promoFilename;
        }

        $savedCourse = $this->infoproductRepository->create($infoproductData);

        return [
            'status' => 'ok',
            'course_id' => $savedCourse['id'] ?? $nextId
        ];
    }

    private function formatFilename($filename)
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = Str::slug($name);
        return $name . '_' . time() . '.' . $extension;
    }
}
