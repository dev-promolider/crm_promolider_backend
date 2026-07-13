<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\PaymentLink as EloquentPaymentLink;
use Promolider\Domain\Marketing\Entities\PaymentLink;
use Promolider\Domain\Marketing\Ports\Out\PaymentLinkRepositoryInterface;

class EloquentPaymentLinkRepository implements PaymentLinkRepositoryInterface
{
    public function list(array $filters = []): array
    {
        $query = EloquentPaymentLink::query()->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['active'])) {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['product_type'])) {
            $query->where('product_type', $filters['product_type']);
        }

        return $query->get()->map(fn($m) => $this->toEntity($m))->toArray();
    }

    public function findById(int $id): ?PaymentLink
    {
        $model = EloquentPaymentLink::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?PaymentLink
    {
        $model = EloquentPaymentLink::where('slug', $slug)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function create(array $data): PaymentLink
    {
        $model = EloquentPaymentLink::create($data);
        return $this->toEntity($model);
    }

    public function update(int $id, array $data): PaymentLink
    {
        $model = EloquentPaymentLink::findOrFail($id);
        $model->update($data);
        return $this->toEntity($model->fresh());
    }

    public function toggleStatus(int $id): PaymentLink
    {
        $model = EloquentPaymentLink::findOrFail($id);
        $model->update(['active' => !$model->active]);
        return $this->toEntity($model->fresh());
    }

    public function delete(int $id): bool
    {
        $model = EloquentPaymentLink::find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }

    public function incrementUsage(int $id): void
    {
        EloquentPaymentLink::where('id', $id)->increment('usage_count');
    }

    private function toEntity(EloquentPaymentLink $model): PaymentLink
    {
        return new PaymentLink(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            productType: $model->product_type,
            productId: $model->product_id,
            amount: (float) $model->amount,
            description: $model->description,
            active: (bool) $model->active,
            usageCount: (int) $model->usage_count,
            createdAt: $model->created_at?->toIso8601String(),
            updatedAt: $model->updated_at?->toIso8601String(),
        );
    }
}
