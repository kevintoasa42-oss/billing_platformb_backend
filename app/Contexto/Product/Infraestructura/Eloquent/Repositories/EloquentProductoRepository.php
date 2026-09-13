<?php

namespace App\Contexto\Product\Infraestructura\Eloquent\Repositories;

use App\Contexto\Product\Dominio\Modelos\Producto;
use App\Contexto\Product\Dominio\Repositorios\ProductoRepositoryInterface;
use App\Contexto\Product\Infraestructura\Eloquent\Mappers\EloquentProductoMapper;
use App\Contexto\Product\Infraestructura\Eloquent\Models\ProductoModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductoRepository implements ProductoRepositoryInterface
{
    public function __construct(
        private EloquentProductoMapper $mapper,
    ) {}

    public function listarPaginado(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        $query = ProductoModel::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('codigo_barras', 'ILIKE', "%{$search}%")
                  ->orWhere('codigo_auxiliar', 'ILIKE', "%{$search}%");
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

    public function obtenerPorId(int $id): ?Producto
    {
        $model = ProductoModel::find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function crear(Producto $producto): Producto
    {
        $model = ProductoModel::create($this->mapper->toModel($producto));

        if (!empty($producto->impuestos)) {
            $this->asignarImpuestos($model->id, $producto->impuestos);
        }

        return $this->mapper->toDomain($model->fresh());
    }

    public function actualizar(Producto $producto): Producto
    {
        $model = ProductoModel::findOrFail($producto->id);
        $model->update($this->mapper->toModel($producto));

        if ($producto->impuestos !== []) {
            $this->asignarImpuestos($model->id, $producto->impuestos);
        }

        return $this->mapper->toDomain($model->fresh());
    }

    public function cambiarEstado(int $id, bool $estado): bool
    {
        return ProductoModel::where('id', $id)->update(['estado' => $estado]) > 0;
    }

    public function asignarImpuestos(int $productoId, array $impuestoIds): void
    {
        \DB::connection('tenant')
            ->table('producto_impuesto')
            ->where('producto_id', $productoId)
            ->delete();

        if (!empty($impuestoIds)) {
            \DB::connection('tenant')
                ->table('producto_impuesto')
                ->insert(
                    array_map(fn ($id) => [
                        'producto_id' => $productoId,
                        'sri_iva_percentage_id' => $id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $impuestoIds)
                );
        }
    }
}
