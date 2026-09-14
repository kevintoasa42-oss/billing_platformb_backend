<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Product\Domain\Models\Product;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductModel;
use App\Context\V3\Modules\Core\Product\Infrastructure\Mappers\ProductMapper;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductMapper $mapper,
    ) {}

    public function all(?string $search = null, int $limit = 500): array
    {
        $query = ProductModel::query()->with('taxAssignments')->limit($limit);

        if ($search !== null && $search !== '') {
            $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%'.$search.'%']);
        }

        $records = $query->orderBy('name')->get();

        return $this->mapper->toDomainList($records);
    }

    public function findByLegacyId(int $legacyId): ?Product
    {
        $record = ProductModel::query()->with('taxAssignments')
            ->where('legacy_id', $legacyId)
            ->first();

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(array $data): Product
    {
        return DB::connection('master_v3')->transaction(function () use ($data): Product {
            $record = ProductModel::query()->create($data);
            $record->refresh();
            $record->load('taxAssignments');

            return $this->mapper->toDomain($record);
        });
    }

    public function update(int $legacyId, array $data): ?Product
    {
        return DB::connection('master_v3')->transaction(function () use ($legacyId, $data): ?Product {
            $record = ProductModel::query()->where('legacy_id', $legacyId)->first();

            if ($record === null) {
                return null;
            }

            $record->update($data);
            $record->refresh();
            $record->load('taxAssignments');

            return $this->mapper->toDomain($record);
        });
    }

    public function setActive(int $legacyId, bool $isActive): ?Product
    {
        return DB::connection('master_v3')->transaction(function () use ($legacyId, $isActive): ?Product {
            $record = ProductModel::query()->where('legacy_id', $legacyId)->first();

            if ($record === null) {
                return null;
            }

            $record->update(['is_active' => $isActive]);
            $record->refresh();
            $record->load('taxAssignments');

            return $this->mapper->toDomain($record);
        });
    }

    public function delete(int $legacyId): ?Product
    {
        return $this->setActive($legacyId, false);
    }

    public function duplicates(array $filters): array
    {
        $exclude = max(0, (int) ($filters['exclude_id'] ?? 0));
        $barcode = trim((string) ($filters['barcode'] ?? '')) ?: null;
        $auxiliaryCode = trim((string) ($filters['auxiliary_code'] ?? '')) ?: null;
        $name = trim((string) ($filters['name'] ?? '')) ?: null;

        return [
            'barcode_exists' => $barcode !== null && ProductModel::query()
                ->where('barcode', $barcode)
                ->where('legacy_id', '<>', $exclude)
                ->exists(),
            'auxiliary_code_exists' => $auxiliaryCode !== null && ProductModel::query()
                ->where('auxiliary_code', $auxiliaryCode)
                ->where('legacy_id', '<>', $exclude)
                ->exists(),
            'name_exists' => $name !== null && ProductModel::query()
                ->whereRaw('lower(name) = lower(?)', [$name])
                ->where('legacy_id', '<>', $exclude)
                ->exists(),
        ];
    }

}
