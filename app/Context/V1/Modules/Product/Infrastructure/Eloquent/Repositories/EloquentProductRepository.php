<?php

namespace App\Context\V1\Modules\Product\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Modules\Product\Domain\Mappers\ProductMapper;
use App\Context\V1\Modules\Product\Domain\Models\Product;
use App\Context\V1\Modules\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Context\V1\Modules\Product\Infrastructure\Eloquent\Mappers\EloquentProductMapper;
use App\Models\ProductModel;
use App\Models\ProductTaxModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        $query = ProductModel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('barcode', 'ILIKE', "%{$search}%")
                  ->orWhere('auxiliary_code', 'ILIKE', "%{$search}%");
            });
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(fn ($m) => EloquentProductMapper::toDomain($m))->toArray();

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
        $model = ProductModel::find($id);

        return $model ? ProductMapper::toDtoArray(EloquentProductMapper::toDomain($model)) : null;
    }

    public function create(Product $product): array
    {
        $model = ProductModel::create(EloquentProductMapper::toModel($product));

        if (!empty($product->taxes)) {
            $this->assignTaxes($model->id, $product->taxes);
        }

        return ProductMapper::toDtoArray(EloquentProductMapper::toDomain($model->fresh()));
    }

    public function update(Product $product): array
    {
        $model = ProductModel::findOrFail($product->id);
        $model->update(EloquentProductMapper::toModel($product));

        if ($product->taxes !== []) {
            $this->assignTaxes($model->id, $product->taxes);
        }

        return ProductMapper::toDtoArray(EloquentProductMapper::toDomain($model->fresh()));
    }

    public function changeStatus(int $id, bool $status): bool
    {
        return ProductModel::where('id', $id)->update(['status' => $status]) > 0;
    }

    /**
     * Syncs product taxes using Eloquent ProductTaxModel.
     * Deletes previous records and creates new ones.
     */
    public function assignTaxes(int $productId, array $taxIds): void
    {
        ProductTaxModel::where('product_id', $productId)->delete();

        if (!empty($taxIds)) {
            ProductTaxModel::insert(
                array_map(fn ($id) => [
                    'product_id' => $productId,
                    'sri_iva_percentage_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $taxIds)
            );
        }
    }
}
