<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Product\Domain\Models\Product;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductModel;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductTaxAssignmentModel;
use App\Context\V3\Modules\Core\Product\Infrastructure\Mappers\ProductMapper;
use App\Context\V3\Modules\Core\SriIva\Infrastructure\Laravel\Eloquent\Models\SriIvaTypeModel;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly ProductMapper $mapper,
    ) {}

    public function all(?string $search = null, int $limit = 500, ?bool $isActive = null): array
    {
        $query = ProductModel::query()->with('taxAssignments')->limit($limit);

        if ($search !== null && $search !== '') {
            $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%'.$search.'%']);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
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
            $sriIvaTypeIds = $data['sri_iva_type_ids'] ?? null;
            unset($data['sri_iva_type_ids']);

            $record = ProductModel::query()->create($data);
            $record->refresh();

            if ($sriIvaTypeIds !== null) {
                $this->syncTaxAssignments($record, $sriIvaTypeIds);
            }

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

            $sriIvaTypeIds = $data['sri_iva_type_ids'] ?? null;
            unset($data['sri_iva_type_ids']);

            $record->update($data);
            $record->refresh();

            if ($sriIvaTypeIds !== null) {
                $this->syncTaxAssignments($record, $sriIvaTypeIds);
            }

            $record->load('taxAssignments');

            return $this->mapper->toDomain($record);
        });
    }

    /**
     * Replace the product's IVA tax assignments with the given SRI IVA type IDs.
     *
     * @param  array<int>  $sriIvaTypeIds
     */
    private function syncTaxAssignments(ProductModel $record, array $sriIvaTypeIds): void
    {
        $record->taxAssignments()->delete();

        if ($sriIvaTypeIds === []) {
            return;
        }

        $ivaTypes = SriIvaTypeModel::query()
            ->whereIn('id', $sriIvaTypeIds)
            ->get()
            ->keyBy('id');

        foreach ($sriIvaTypeIds as $typeId) {
            $ivaType = $ivaTypes->get($typeId);
            if ($ivaType === null) {
                continue;
            }

            ProductTaxAssignmentModel::query()->create([
                'product_id' => $record->id,
                'sri_iva_type_id' => $ivaType->id,
                'tax_name' => $ivaType->name,
                'percentage' => $ivaType->percentage,
                'sri_code' => $ivaType->sri_code,
                'is_active' => true,
            ]);
        }
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
