<?php

namespace App\Context\V1\Product\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Product\Domain\Models\Product;
use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;
use App\Context\V1\Product\Infrastructure\Eloquent\Mappers\EloquentProductMapper;
use App\Context\V1\Product\Infrastructure\Eloquent\Models\ProductModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private EloquentProductMapper $mapper,
    ) {}

    public function listarPaginado(int $page = 1, int $perPage = 15, ?string $search = null): array
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

        $data = $paginator->getCollection()->map(fn ($m) => $this->mapper->toDomain($m))->toArray();

        return [
            'data' => $data,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
        ];
    }

    public function obtenerPorId(int $id): ?Product
    {
        $model = ProductModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function crear(Product $product): Product
    {
        $model = ProductModel::create($this->mapper->toModel($product));

        if (!empty($product->taxes)) {
            $this->assignTaxes($model->id, $product->taxes);
        }

        return $this->mapper->toDomain($model->fresh());
    }

    public function actualizar(Product $product): Product
    {
        $model = ProductModel::findOrFail($product->id);
        $model->update($this->mapper->toModel($product));

        if ($product->taxes !== []) {
            $this->assignTaxes($model->id, $product->taxes);
        }

        return $this->mapper->toDomain($model->fresh());
    }

    public function cambiarEstado(int $id, bool $status): bool
    {
        return ProductModel::where('id', $id)->update(['status' => $status]) > 0;
    }

    public function assignTaxes(int $productId, array $taxIds): void
    {
        \DB::connection('tenant')
            ->table('product_tax')
            ->where('product_id', $productId)
            ->delete();

        if (!empty($taxIds)) {
            \DB::connection('tenant')
                ->table('product_tax')
                ->insert(
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
