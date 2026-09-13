<?php

namespace App\Context\V1\Signature\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Signature\Domain\Mappers\SignatureMapper;
use App\Context\V1\Signature\Domain\Models\Signature;
use App\Context\V1\Signature\Domain\Repositories\SignatureRepositoryInterface;
use App\Context\V1\Signature\Infrastructure\Eloquent\Mappers\EloquentSignatureMapper;
use App\Models\SignatureModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentSignatureRepository implements SignatureRepositoryInterface
{
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        $query = SignatureModel::query()->with('carrier');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'ILIKE', "%{$search}%")
                  ->orWhere('environment', 'ILIKE', "%{$search}%")
                  ->orWhereHas('carrier', function ($cq) use ($search) {
                      $cq->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('ruc', 'ILIKE', "%{$search}%");
                  });
            });
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(fn ($m) => EloquentSignatureMapper::toDomain($m))->toArray();

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
        $model = SignatureModel::with('carrier')->find($id);

        return $model ? SignatureMapper::toDtoArray(EloquentSignatureMapper::toDomain($model)) : null;
    }

    public function create(Signature $signature): array
    {
        $model = SignatureModel::create(EloquentSignatureMapper::toModel($signature));

        return SignatureMapper::toDtoArray(EloquentSignatureMapper::toDomain($model->fresh()));
    }

    public function update(Signature $signature): array
    {
        $model = SignatureModel::findOrFail($signature->id);
        $model->update(EloquentSignatureMapper::toModel($signature));

        return SignatureMapper::toDtoArray(EloquentSignatureMapper::toDomain($model->fresh()));
    }

    public function changeStatus(int $id, bool $status): bool
    {
        return SignatureModel::where('id', $id)->update(['status' => $status]) > 0;
    }
}
