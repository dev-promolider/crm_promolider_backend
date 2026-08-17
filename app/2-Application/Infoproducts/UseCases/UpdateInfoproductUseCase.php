<?php

namespace Promolider\Application\Infoproducts\UseCases;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Illuminate\Support\Str;
use App\Models\User;
use Exception;

class UpdateInfoproductUseCase
{
    public function __construct(
        private InfoproductRepositoryInterface $infoproductRepository
    ) {}

    public function execute(int $id, array $data, User $user, $coverFile = null, $promoFile = null): array
    {
        $infoproduct = $this->infoproductRepository->findCourseById($id);

        if (!$infoproduct) {
            throw new Exception('Infoproducto no encontrado.');
        }

        if ($infoproduct->getUserId() !== $user->id) {
            throw new Exception('No tienes permisos para editar este curso.');
        }

        $infoproductData = [
            'title' => $data['title'] ?? $infoproduct->getTitle(),
            'slug' => isset($data['title']) ? Str::slug($data['title']) : $infoproduct->getSlug(),
            'description' => $data['description'] ?? $infoproduct->getDescription(),
            'price_base' => $data['price_base'] ?? $infoproduct->getPriceBase(),
            'old_price' => $data['old_price'] ?? null,
            'price' => $data['price'] ?? $infoproduct->getPrice(),
            'course_about' => $data['course_about'] ?? $infoproduct->getCourseAbout(),
            'will_learn' => $data['will_learn'] ?? $infoproduct->getWillLearn(),
            'prev_knowledge' => $data['prev_knowledge'] ?? $infoproduct->getPrevKnowledge(),
            'course_for' => $data['course_for'] ?? $infoproduct->getCourseFor(),
            'language' => $data['language'] ?? 'Español',
        ];

        if (isset($data['product_type_id']) && $data['product_type_id'] === '1') {
            if (isset($data['id_categories'])) $infoproductData['id_categories'] = $data['id_categories'];
            if (isset($data['course_level_id'])) $infoproductData['course_level_id'] = $data['course_level_id'];
            if (isset($data['course_time'])) $infoproductData['course_time'] = $data['course_time'];
        }

        if (isset($data['includes'])) {
            $infoproductData['includes'] = json_decode($data['includes'], true);
        }

        if (isset($data['certificate'])) {
            $infoproductData['certificate'] = ($data['certificate'] === 'true' || $data['certificate'] == 1) ? 1 : 0;
        }

        // Initialize S3 Client
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
            $portadaPath = 'courses/' . $user->id . '/' . $id . '/cover/';
            
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
            $promoPath = 'courses/' . $user->id . '/' . $id . '/promo/';
            
            $uploader = new \Aws\S3\MultipartUploader($s3Client, $promoFile->getRealPath(), [
                'bucket' => $bucket,
                'key' => $promoPath . $promoFilename,
                'ACL' => 'public-read',
            ]);
            $uploader->upload();

            $infoproductData['videoimg'] = $promoPath . $promoFilename;
        }

        $success = $this->infoproductRepository->update($id, $infoproductData);

        if (!$success) {
            throw new Exception('No se pudo actualizar el infoproducto en la base de datos.');
        }

        return [
            'status' => 'success',
            'message' => 'Curso actualizado correctamente.'
        ];
    }

    private function formatFilename(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $safeName = Str::slug($name);
        return $safeName . '-' . time() . '.' . $extension;
    }
}
