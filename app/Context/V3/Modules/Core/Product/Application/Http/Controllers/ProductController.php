<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductCreateDTO;
use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductUpdateDTO;
use App\Context\V3\Modules\Core\Product\Application\Http\Requests\ProductCreateRequest;
use App\Context\V3\Modules\Core\Product\Application\Http\Requests\ProductIndexRequest;
use App\Context\V3\Modules\Core\Product\Application\Http\Requests\ProductUpdateRequest;
use App\Context\V3\Modules\Core\Product\Application\UseCases\ProductUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ProductController
{
    use ApiResponse;

    public function __construct(
        private readonly ProductUseCase $useCase,
    ) {}

    public function index(ProductIndexRequest $request): JsonResponse
    {
        $products = $this->useCase->all(
            $request->searchTerm(),
            $request->perPage(),
            $request->activeFilter(),
        );

        return $this->success(
            array_map(fn ($p): array => $p->toArray(), $products),
            'Productos cargados.',
        );
    }

    public function duplicates(Request $request): JsonResponse
    {
        $result = $this->useCase->duplicates($request->query());

        return $this->success($result, 'Disponibilidad del producto verificada.');
    }

    public function store(ProductCreateRequest $request): JsonResponse
    {
        $dto = ProductCreateDTO::fromArray($request->validated());
        $product = $this->useCase->create($dto);

        return $this->success($product->toArray(), 'Producto creado.', Response::HTTP_CREATED);
    }

    public function update(ProductUpdateRequest $request, int $id): JsonResponse
    {
        $dto = ProductUpdateDTO::fromArray($request->validated());
        $product = $this->useCase->update($id, $dto);

        if ($product === null) {
            return $this->error('No se encontró el producto solicitado.', Response::HTTP_NOT_FOUND, [
                'code' => 'product_not_found',
            ]);
        }

        return $this->success($product->toArray(), 'Producto actualizado.');
    }

    public function setStatus(Request $request, int $id): JsonResponse
    {
        $isActive = (bool) $request->input('is_active', true);
        $product = $this->useCase->setActive($id, $isActive);

        if ($product === null) {
            return $this->error('No se encontró el producto solicitado.', Response::HTTP_NOT_FOUND, [
                'code' => 'product_not_found',
            ]);
        }

        return $this->success($product->toArray(), 'Estado del producto actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        $product = $this->useCase->delete($id);

        if ($product === null) {
            return $this->error('No se encontró el producto solicitado.', Response::HTTP_NOT_FOUND, [
                'code' => 'product_not_found',
            ]);
        }

        return $this->success($product->toArray(), 'Producto desactivado.');
    }
}
