<?php

namespace App\Context\V1\Modules\Product\Application\Http\Controllers;

use App\Context\V1\Modules\Product\Application\Http\Requests\ChangeProductStatusRequest;
use App\Context\V1\Modules\Product\Application\Http\Requests\CreateProductRequest;
use App\Context\V1\Modules\Product\Application\Http\Requests\UpdateProductRequest;
use App\Context\V1\Modules\Product\Application\UseCases\ChangeProductStatusUseCase;
use App\Context\V1\Modules\Product\Application\UseCases\CreateProductUseCase;
use App\Context\V1\Modules\Product\Application\UseCases\GetProductByIdUseCase;
use App\Context\V1\Modules\Product\Application\UseCases\ListProductsUseCase;
use App\Context\V1\Modules\Product\Application\UseCases\UpdateProductUseCase;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateProductUseCase $createUseCase,
        private ListProductsUseCase $listUseCase,
        private GetProductByIdUseCase $getByIdUseCase,
        private UpdateProductUseCase $updateUseCase,
        private ChangeProductStatusUseCase $changeStatusUseCase,
    ) {}

    /**
     * GET /api/products?page=1&perPage=15&search=...
     * Paginated list of products.
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('perPage', 15);
        $search = $request->query('search');

        $result = $this->listUseCase->execute($page, $perPage, $search);

        return $this->successResponse($result);
    }

    /**
     * GET /api/products/{id}
     * Get a product by ID.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->getByIdUseCase->execute($id);

        if (!$product) {
            return $this->errorResponse('Product not found.', 404);
        }

        return $this->successResponse($product);
    }

    /**
     * POST /api/products
     * Create a product.
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $dto = CreateProductRequest::toDTO($request->validated());

        return $this->successResponse($this->createUseCase->execute($dto), 201);
    }

    /**
     * PUT/PATCH /api/products/{id}
     * Update a product.
     */
    public function update(int $id, UpdateProductRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), ['id' => $id]);
        $dto = UpdateProductRequest::toDTO($data);

        try {
            return $this->successResponse($this->updateUseCase->execute($dto));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product not found.', 404);
        }
    }

    /**
     * PATCH /api/products/{id}/status
     * Change the status (active/inactive) of a product.
     */
    public function changeStatus(int $id, ChangeProductStatusRequest $request): JsonResponse
    {
        $status = $request->validated()['status'];

        $result = $this->changeStatusUseCase->execute($id, $status);

        if (!$result) {
            return $this->errorResponse('Product not found.', 404);
        }

        return $this->successResponse('Status updated successfully.');
    }
}
