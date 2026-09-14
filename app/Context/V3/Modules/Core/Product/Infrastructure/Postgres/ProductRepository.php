<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Product\Domain\Models\Product;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductModel;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(?string $search = null, int $limit = 500): array
    {
        $query = ProductModel::query()->with('taxAssignments')->limit($limit);

        if ($search !== null && $search !== '') {
            $query->whereRaw('LOWER(name) LIKE LOWER(?)', ['%'.$search.'%']);
        }

        $records = $query->orderBy('name')->get();

        return $records->map(fn (ProductModel $record): Product => $this->toDomain($record))->all();
    }

    public function findByLegacyId(int $legacyId): ?Product
    {
        $record = ProductModel::query()->with('taxAssignments')
            ->where('legacy_id', $legacyId)
            ->first();

        return $record !== null ? $this->toDomain($record) : null;
    }

    public function create(array $data): Product
    {
        return DB::connection('master_v3')->transaction(function () use ($data): Product {
            $record = ProductModel::query()->create($data);
            $record->refresh();
            $record->load('taxAssignments');

            return $this->toDomain($record);
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

            return $this->toDomain($record);
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

            return $this->toDomain($record);
        });
    }

    public function delete(int $legacyId): ?Product
    {
        return $this->setActive($legacyId, false);
    }

    public function duplicates(array $filters): array
    {
        $exclude = max(0, (int) ($filters['exclude_id'] ?? 0));
        $barcode = $this->nullableText($filters['barcode'] ?? null);
        $auxiliaryCode = $this->nullableText($filters['auxiliary_code'] ?? null);
        $name = $this->nullableText($filters['name'] ?? null);

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

    private function toDomain(ProductModel $record): Product
    {
        $taxes = [];
        if ($record->relationLoaded('taxAssignments')) {
            $taxes = $record->taxAssignments->where('is_active', true)->map(fn ($t): array => [
                'id' => (int) $t->legacy_id,
                'product_id' => (int) $record->legacy_id,
                'sri_iva_type_id' => (int) $t->sri_iva_type_id,
                'tax_name' => $t->tax_name,
                'percentage' => (string) $t->percentage,
                'sri_code' => $t->sri_code,
                'is_active' => (bool) $t->is_active,
            ])->values()->all();
        }

        return new Product(
            uuid: (string) $record->id,
            id: (int) $record->legacy_id,
            name: $record->name,
            referencePrice: (string) $record->unit_price,
            unitPrice: (string) $record->unit_price,
            sriPrincipalCode: $record->sri_principal_code,
            isActive: (bool) $record->is_active,
            type: $record->type,
            barcode: $record->barcode,
            auxiliaryCode: $record->auxiliary_code,
            otherCode: $record->other_code,
            description: $record->description,
            activityId: $record->activity_id,
            taxes: $taxes,
        );
    }

    private function nullableText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
