<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductTaxCreateDTO;
use App\Context\V3\Modules\Core\Product\Application\Http\Requests\ProductTaxCreateRequest;
use App\Context\V3\Modules\Core\Product\Application\UseCases\ProductTaxUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProductTaxController
{
    use ApiResponse;

    public function __construct(
        private readonly ProductTaxUseCase $useCase,
    ) {}

    public function store(ProductTaxCreateRequest $request, int $product): JsonResponse
    {
        $dto = ProductTaxCreateDTO::fromArray($request->validated());
        $tax = $this->useCase->create($product, $dto);

        return $this->success($tax->toArray(), 'Impuesto del producto guardado.', Response::HTTP_CREATED);
    }

    public function destroy(int $product, int $tax): JsonResponse
    {
        $deleted = $this->useCase->delete($product, $tax);

        if (! $deleted) {
            return $this->error('No se encontró el impuesto solicitado.', Response::HTTP_NOT_FOUND, [
                'code' => 'product_tax_not_found',
            ]);
        }

        return $this->success(['deleted' => true], 'Impuesto del producto eliminado.');
    }
}
