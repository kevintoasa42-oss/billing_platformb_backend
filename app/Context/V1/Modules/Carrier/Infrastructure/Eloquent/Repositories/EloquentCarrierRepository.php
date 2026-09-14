<?php

namespace App\Context\V1\Modules\Carrier\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Modules\Carrier\Domain\Mappers\CarrierMapper;
use App\Context\V1\Modules\Carrier\Domain\Models\Carrier;
use App\Context\V1\Modules\Carrier\Domain\Repositories\CarrierRepositoryInterface;
use App\Context\V1\Modules\Carrier\Infrastructure\Eloquent\Mappers\EloquentCarrierMapper;
use App\Models\CarrierModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCarrierRepository implements CarrierRepositoryInterface
{
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        $query = CarrierModel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('ruc', 'ILIKE', "%{$search}%")
                  ->orWhere('tradename', 'ILIKE', "%{$search}%");
            });
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(fn ($m) => EloquentCarrierMapper::toDomain($m))->toArray();

        return [
            'data' => $data,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function getById(int $id): ?array
    {
        $model = CarrierModel::find($id);

        return $model ? CarrierMapper::toDtoArray(EloquentCarrierMapper::toDomain($model)) : null;
    }

    public function create(Carrier $carrier): array
    {
        $model = CarrierModel::create(EloquentCarrierMapper::toModel($carrier));

        return CarrierMapper::toDtoArray(EloquentCarrierMapper::toDomain($model->fresh()));
    }

    public function update(Carrier $carrier): array
    {
        $model = CarrierModel::findOrFail($carrier->id);
        $model->update(EloquentCarrierMapper::toModel($carrier));

        return CarrierMapper::toDtoArray(EloquentCarrierMapper::toDomain($model->fresh()));
    }

    public function changeStatus(int $id, bool $status): bool
    {
        return CarrierModel::where('id', $id)->update(['status' => $status]) > 0;
    }
}
