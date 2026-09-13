<?php

namespace App\Context\V1\BranchOffices\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V1\BranchOffices\Domain\Mappers\BranchOfficeMapperInterface;
use App\Context\V1\BranchOffices\Domain\Models\BranchOffice;
use App\Context\V1\BranchOffices\Domain\Repositories\BranchOfficeRepositoryInterface;
use App\Context\V1\BranchOffices\Infrastructure\Laravel\Eloquent\Models\BranchOfficeModel;

final class EloquentBranchOfficeRepository implements BranchOfficeRepositoryInterface
{
    public function __construct(private BranchOfficeMapperInterface $mapper) {}

    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $query = BranchOfficeModel::query();
        if (! empty($filters['search'])) {
            $value = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('name', 'like', $value)->orWhere('code_sri', 'like', $value)->orWhere('type', 'like', $value));
        }
        foreach (['status', 'default'] as $field) {
            if (array_key_exists($field, $filters) && $filters[$field] !== null) {
                $query->where($field, (bool) $filters[$field]);
            }
        }
        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()->map(fn (BranchOfficeModel $model) => $this->mapper->toDomain($model->toArray()))->all(),
            'total' => $paginator->total(), 'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(), 'lastPage' => $paginator->lastPage(),
        ];
    }

    public function findById(int $id): ?BranchOffice
    {
        $model = BranchOfficeModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function create(BranchOffice $branchOffice): BranchOffice
    {
        $model = BranchOfficeModel::create($this->mapper->toPersistence($branchOffice));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function update(BranchOffice $branchOffice): BranchOffice
    {
        $model = BranchOfficeModel::findOrFail($branchOffice->id);
        $model->update($this->mapper->toPersistence($branchOffice));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function delete(int $id): bool
    {
        return BranchOfficeModel::find($id)?->delete() ?? false;
    }
}
