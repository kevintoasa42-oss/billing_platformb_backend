<?php

namespace App\Context\V1\Partners\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V1\Partners\Domain\Mappers\PartnerMapperInterface;
use App\Context\V1\Partners\Domain\Models\Partner;
use App\Context\V1\Partners\Domain\Repositories\PartnerRepositoryInterface;
use App\Context\V1\Partners\Infrastructure\Laravel\Eloquent\Models\PartnerModel;

final class EloquentPartnerRepository implements PartnerRepositoryInterface
{
    public function __construct(private readonly PartnerMapperInterface $mapper) {}

    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $query = PartnerModel::query();

        if (isset($filters['search']) && $filters['search'] !== '') {
            $value = '%'.$filters['search'].'%';
            $query->where(static fn ($builder) => $builder
                ->where('identification_number', 'like', $value)
                ->orWhere('name', 'like', $value)
                ->orWhere('last_name', 'like', $value)
                ->orWhere('email', 'like', $value));
        }

        if (isset($filters['identification_type']) && $filters['identification_type'] !== '') {
            $query->where('identification_type', $filters['identification_type']);
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== null) {
            $query->where('status', (bool) $filters['status']);
        }

        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()->map(fn (PartnerModel $model) => $this->mapper->toDomain($model->toArray()))->all(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function findById(int $id): ?Partner
    {
        $model = PartnerModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function create(Partner $partner): Partner
    {
        $model = PartnerModel::create($this->mapper->toPersistence($partner));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function update(Partner $partner): Partner
    {
        $model = PartnerModel::findOrFail($partner->id);
        $model->update($this->mapper->toPersistence($partner));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function delete(int $id): bool
    {
        return PartnerModel::find($id)?->delete() ?? false;
    }
}
