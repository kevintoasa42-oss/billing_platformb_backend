<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Product\Domain\Models\ProductTax;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductTaxRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductModel;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductTaxAssignmentModel;
use App\Context\V3\Modules\Core\Product\Infrastructure\Mappers\ProductTaxMapper;
use Illuminate\Support\Facades\DB;

class ProductTaxRepository implements ProductTaxRepositoryInterface
{
    public function __construct(
        private readonly ProductTaxMapper $mapper,
    ) {}

    public function create(int $productLegacyId, array $data): ?ProductTax
    {
        return DB::connection('master_v3')->transaction(function () use ($productLegacyId, $data): ?ProductTax {
            $product = ProductModel::query()->where('legacy_id', $productLegacyId)->first();

            if ($product === null) {
                return null;
            }

            $typeId = (int) ($data['sri_iva_type_id'] ?? 0);
            $taxName = $data['tax_name'] ?? 'IVA';
            $percentage = $data['percentage'] ?? '0';
            $sriCode = $data['sri_code'] ?? '';

            $existing = ProductTaxAssignmentModel::query()
                ->where('tenant_id', $product->tenant_id)
                ->where('product_id', $product->id)
                ->where('sri_iva_type_id', $typeId)
                ->first();

            if ($existing !== null) {
                $existing->update([
                    'tax_name' => $taxName,
                    'percentage' => $percentage,
                    'sri_code' => $sriCode,
                    'is_active' => true,
                ]);
                $existing->refresh();

                return $this->mapper->toDomain($existing, $productLegacyId);
            }

            $record = ProductTaxAssignmentModel::query()->create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'sri_iva_type_id' => $typeId,
                'tax_name' => $taxName,
                'percentage' => $percentage,
                'sri_code' => $sriCode,
                'is_active' => true,
            ]);
            $record->refresh();

            return $this->mapper->toDomain($record, $productLegacyId);
        });
    }

    public function delete(int $productLegacyId, int $taxId): bool
    {
        return (bool) DB::connection('master_v3')->transaction(function () use ($productLegacyId, $taxId): int {
            $product = ProductModel::query()->where('legacy_id', $productLegacyId)->first();

            if ($product === null) {
                return 0;
            }

            return ProductTaxAssignmentModel::query()
                ->where('tenant_id', $product->tenant_id)
                ->where('product_id', $product->id)
                ->where(function ($query) use ($taxId): void {
                    $query->where('legacy_id', $taxId)->orWhere('sri_iva_type_id', $taxId);
                })
                ->update(['is_active' => false]);
        });
    }

    public function findByProductAndType(int $productLegacyId, int $sriIvaTypeId): ?ProductTax
    {
        $product = ProductModel::query()->where('legacy_id', $productLegacyId)->first();

        if ($product === null) {
            return null;
        }

        $record = ProductTaxAssignmentModel::query()
            ->where('tenant_id', $product->tenant_id)
            ->where('product_id', $product->id)
            ->where('sri_iva_type_id', $sriIvaTypeId)
            ->where('is_active', true)
            ->first();

        return $record !== null ? $this->mapper->toDomain($record, $productLegacyId) : null;
    }

}
