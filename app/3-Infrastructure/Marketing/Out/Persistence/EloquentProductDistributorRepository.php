<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\MasterclassDistributor;
use App\Models\EbookDistributor;
use App\Models\MiniCourseDistributor;
use Promolider\Domain\Marketing\Entities\ProductDistributor;
use Promolider\Domain\Marketing\Ports\Out\ProductDistributorRepositoryInterface;

class EloquentProductDistributorRepository implements ProductDistributorRepositoryInterface
{
    private function getModel(string $productType): string
    {
        return match ($productType) {
            'masterclass' => MasterclassDistributor::class,
            'ebook' => EbookDistributor::class,
            'mini-course', 'minicourse' => MiniCourseDistributor::class,
            default => throw new \InvalidArgumentException("Invalid product type: {$productType}"),
        };
    }

    private function getProductForeignKey(string $productType): string
    {
        return match ($productType) {
            'masterclass' => 'masterclass_id',
            'ebook' => 'ebook_id',
            'mini-course', 'minicourse' => 'mini_course_id',
            default => throw new \InvalidArgumentException("Invalid product type: {$productType}"),
        };
    }

    private function getRegistrationRoute(string $productType): string
    {
        return match ($productType) {
            'masterclass' => '/register-masterclass',
            'ebook' => '/ebook/register',
            'mini-course', 'minicourse' => '/mini-course/register',
            default => '/register',
        };
    }

    public function createInvitation(string $productType, int $productId, int $userId, string $code, string $expiresAt): ProductDistributor
    {
        $modelClass = $this->getModel($productType);
        $foreignKey = $this->getProductForeignKey($productType);

        $model = $modelClass::create([
            'user_id' => $userId,
            $foreignKey => $productId,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        return $this->toEntity($model, $productType, $code);
    }

    public function findExistingInvitation(string $productType, int $productId, int $userId): ?ProductDistributor
    {
        $modelClass = $this->getModel($productType);
        $foreignKey = $this->getProductForeignKey($productType);

        $model = $modelClass::where('user_id', $userId)
            ->where($foreignKey, $productId)
            ->where('code', '!=', '0') // '0' indica asignación directa (no invitación)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model, $productType, $model->code);
    }

    public function findByCode(string $productType, string $code): ?ProductDistributor
    {
        $modelClass = $this->getModel($productType);

        $model = $modelClass::where('code', $code)->first();
        if (!$model) {
            return null;
        }

        $foreignKey = $this->getProductForeignKey($productType);
        $productId = $model->{$foreignKey};

        return $this->toEntity($model, $productType, $code, $productId);
    }

    private function toEntity($model, string $productType, string $code, ?int $productId = null): ProductDistributor
    {
        $foreignKey = $this->getProductForeignKey($productType);
        $actualProductId = $productId ?? $model->{$foreignKey};
        $route = $this->getRegistrationRoute($productType);
        $link = url("{$route}?invitation_code={$code}");

        return new ProductDistributor(
            id: $model->id,
            userId: $model->user_id,
            productId: $actualProductId,
            productType: $productType,
            code: $code,
            expiresAt: $model->expires_at?->toIso8601String(),
            invitationLink: $link,
            exists: true,
        );
    }
}
