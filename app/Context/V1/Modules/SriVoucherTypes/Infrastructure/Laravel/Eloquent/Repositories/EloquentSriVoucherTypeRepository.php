<?php

namespace App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V1\Modules\SriVoucherTypes\Domain\Mappers\SriVoucherTypeMapperInterface;
use App\Context\V1\Modules\SriVoucherTypes\Domain\Models\SriVoucherType;
use App\Context\V1\Modules\SriVoucherTypes\Domain\Repositories\SriVoucherTypeRepositoryInterface;
use App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Eloquent\Models\SriVoucherTypeModel;

final class EloquentSriVoucherTypeRepository implements SriVoucherTypeRepositoryInterface
{
    public function __construct(private readonly SriVoucherTypeMapperInterface $mapper) {}

    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $query = SriVoucherTypeModel::query();

        if (! empty($filters['search'])) {
            $value = '%'.$filters['search'].'%';
            $query->where(fn ($builder) => $builder
                ->where('document', 'ilike', $value)
                ->orWhere('code', 'ilike', $value));
        }
        if (! empty($filters['code'])) {
            $query->where('code', $filters['code']);
        }
        if (array_key_exists('retention', $filters) && $filters['retention'] !== null) {
            $query->where('retention', (bool) $filters['retention']);
        }
        if (! empty($filters['current'])) {
            $today = now()->toDateString();
            $query->whereDate('start_date', '<=', $today)
                ->where(fn ($builder) => $builder->whereNull('end_date')->orWhereDate('end_date', '>=', $today));
        }

        $paginator = $query->orderBy('code')->orderByDesc('start_date')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (SriVoucherTypeModel $model) => $this->mapper->toDomain($model->toArray()))
                ->all(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function findById(int $id): ?SriVoucherType
    {
        $model = SriVoucherTypeModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function findCurrentByCode(string $code): ?SriVoucherType
    {
        $today = now()->toDateString();
        $model = SriVoucherTypeModel::query()
            ->where('code', $code)
            ->whereDate('start_date', '<=', $today)
            ->where(fn ($builder) => $builder->whereNull('end_date')->orWhereDate('end_date', '>=', $today))
            ->orderByDesc('start_date')
            ->first();

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function create(SriVoucherType $voucherType): SriVoucherType
    {
        $model = SriVoucherTypeModel::create($this->mapper->toPersistence($voucherType));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function update(SriVoucherType $voucherType): SriVoucherType
    {
        $model = SriVoucherTypeModel::findOrFail($voucherType->id);
        $model->update($this->mapper->toPersistence($voucherType));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function delete(int $id): bool
    {
        return SriVoucherTypeModel::find($id)?->delete() ?? false;
    }
}
