<?php

namespace Promolider\Infrastructure\Infoproducts\Out\Persistence;

use Promolider\Domain\Infoproducts\Ports\Out\InfoproductRepositoryInterface;
use Promolider\Domain\Infoproducts\Entities\Infoproduct as InfoproductEntity;
use App\Models\Infoproduct\Infoproduct as EloquentInfoproduct; // Depende de que tu modelo Eloquent exista en App\Models

use Illuminate\Support\Facades\Log;

class EloquentInfoproductRepository implements InfoproductRepositoryInterface
{
    public function findPurchasedByUserId(string $userId): array
    {
        $infoproducts = EloquentInfoproduct::join('purchased_courses', 'courses.id', '=', 'purchased_courses.course_id')
            ->where('purchased_courses.user_id', $userId)
            ->select('courses.*')
            ->get();

        return $infoproducts->map(function ($infoproduct) {
            return new InfoproductEntity(
                $infoproduct->id,
                $infoproduct->product_type_id,
                $infoproduct->instructor_signature_path,
                $infoproduct->user_id,
                $infoproduct->id_categories,
                $infoproduct->title,
                $infoproduct->slug,
                $infoproduct->area,
                $infoproduct->description,
                $infoproduct->currency,
                $infoproduct->price,
                $infoproduct->ranking_by_user,
                $infoproduct->status,
                $infoproduct->portada,
                $infoproduct->url_portada,
                $infoproduct->course_about,
                $infoproduct->will_learn,
                $infoproduct->prev_knowledge,
                $infoproduct->course_for,
                $infoproduct->course_time,
                $infoproduct->course_level_id,
                $infoproduct->months,
                $infoproduct->path_url,
                $infoproduct->price_base,
                $infoproduct->certificate,
                $infoproduct->certificate_template_id,
                $infoproduct->marketplace_listed,
                $infoproduct->old_price,
                $infoproduct->language,
                $infoproduct->created_at ? (string) $infoproduct->created_at : null,
                $infoproduct->updated_at ? (string) $infoproduct->updated_at : null
            );
        })->toArray();
    }

    public function findCreatedByUserIdPaginated(
        int $userId,
        int $page,
        int $perPage,
        ?string $search = null,
        ?int $productTypeId = null,
        ?int $status = null
    ): array
    {
        $query = EloquentInfoproduct::where('user_id', $userId);

        // Filtro por búsqueda si se proporciona
        $query->when($search, function ($q) use ($search) {
            $q->where('title', 'like', '%' . $search . '%');
        });

        // Filtro por tipo de producto si se proporciona
        $query->when($productTypeId, function ($q) use ($productTypeId) {
            $q->where('product_type_id', $productTypeId);
        });

        // Filtro por estado si se proporciona
        $query->when($status !== null, function ($q) use ($status) {
            $q->where('status', $status);
        });

        // Paginación
        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        // Mapeamos los modelos Eloquent (Infraestructura) a las Entidades (Dominio)
        $entities = collect($paginator->items())->map(function ($infoproduct) {
            return new InfoproductEntity(
                $infoproduct->id,
                $infoproduct->product_type_id,
                $infoproduct->instructor_signature_path,
                $infoproduct->user_id,
                $infoproduct->id_categories,
                $infoproduct->title,
                $infoproduct->slug,
                $infoproduct->area,
                $infoproduct->description,
                $infoproduct->currency,
                $infoproduct->price,
                $infoproduct->ranking_by_user,
                $infoproduct->status,
                $infoproduct->portada,
                $infoproduct->url_portada,
                $infoproduct->course_about,
                $infoproduct->will_learn,
                $infoproduct->prev_knowledge,
                $infoproduct->course_for,
                $infoproduct->course_time,
                $infoproduct->course_level_id,
                $infoproduct->months,
                $infoproduct->path_url,
                $infoproduct->price_base,
                $infoproduct->certificate,
                $infoproduct->certificate_template_id,
                $infoproduct->marketplace_listed,
                $infoproduct->old_price,
                $infoproduct->language,
                $infoproduct->created_at ? (string) $infoproduct->created_at : null,
                $infoproduct->updated_at ? (string) $infoproduct->updated_at : null
            );
        })->toArray();

        // Devolvemos un array con los datos paginados y las entidades
        return [
            'data' => $entities,
            'meta' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    public function findCourseById(int $courseId): ?InfoproductEntity
    {
        $infoproduct = EloquentInfoproduct::find($courseId);

        if (!$infoproduct) {
            return null;
        }

        return new InfoproductEntity(
            $infoproduct->id,
            $infoproduct->product_type_id,
            $infoproduct->instructor_signature_path,
            $infoproduct->user_id,
            $infoproduct->id_categories,
            $infoproduct->title,
            $infoproduct->slug,
            $infoproduct->area,
            $infoproduct->description,
            $infoproduct->currency,
            $infoproduct->price,
            $infoproduct->ranking_by_user,
            $infoproduct->status,
            $infoproduct->portada,
            $infoproduct->url_portada,
            $infoproduct->course_about,
            $infoproduct->will_learn,
            $infoproduct->prev_knowledge,
            $infoproduct->course_for,
            $infoproduct->course_time,
            $infoproduct->course_level_id,
            $infoproduct->months,
            $infoproduct->path_url,
            $infoproduct->price_base,
            $infoproduct->certificate,
            $infoproduct->certificate_template_id,
            $infoproduct->marketplace_listed,
            $infoproduct->old_price,
            $infoproduct->language,
            $infoproduct->created_at ? (string) $infoproduct->created_at : null,
            $infoproduct->updated_at ? (string) $infoproduct->updated_at : null
        );
    }

    public function getNextId(): int
    {
        $id = \Illuminate\Support\Facades\DB::select("SHOW TABLE STATUS LIKE 'courses'");
        if (!empty($id)) {
            return $id[0]->Auto_increment;
        }
        return 1;
    }

    public function create(array $data): array
    {
        $infoproduct = EloquentInfoproduct::create($data);
        return $infoproduct->toArray();
    }

    public function update(int $id, array $data): bool
    {
        try {
            $infoproduct = EloquentInfoproduct::find($id);
            if (!$infoproduct) {
                return false;
            }
            return $infoproduct->update($data);
        } catch (\Exception $e) {
            Log::error('Error actualizando el infoproducto: ' . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $infoproduct = EloquentInfoproduct::find($id);
            if (!$infoproduct) {
                return false;
            }
            // Soft delete - set status to 0 (inactive)
            $infoproduct->status = 0;
            return $infoproduct->save();
        } catch (\Exception $e) {
            Log::error('Error eliminando (inactivando) el infoproducto: ' . $e->getMessage());
            return false;
        }
    }
}
