<?php

namespace App\Context\Product\Application\Http\Controllers;

use App\Context\Product\Application\UseCases\UpdateProductUseCase;
use App\Context\Product\Application\UseCases\ChangeProductStatusUseCase;
use App\Context\Product\Application\UseCases\CreateProductUseCase;
use App\Context\Product\Application\UseCases\ListProductsUseCase;
use App\Context\Product\Application\UseCases\GetProductByIdUseCase;
use App\Context\Product\Application\Http\Requests\UpdateProductRequest;
use App\Context\Product\Application\Http\Requests\ChangeProductStatusRequest;
use App\Context\Product\Application\Http\Requests\CreateProductRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateProductUseCase $crearCasoUso,
        private ListProductsUseCase $listarCasoUso,
        private GetProductByIdUseCase $obtenerPorIdCasoUso,
        private UpdateProductUseCase $actualizarCasoUso,
        private ChangeProductStatusUseCase $cambiarEstadoCasoUso,
    ) {}

    /**
     * GET /api/products?page=1&perPage=15&search=...
     * Lista paginada de products.
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('perPage', 15);
        $search = $request->query('search');

        $resultado = $this->listarCasoUso->ejecutar($page, $perPage, $search);

        return $this->successResponse($resultado);
    }

    /**
     * GET /api/products/{id}
     * Obtiene un product por ID.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->obtenerPorIdCasoUso->ejecutar($id);

        if (!$product) {
            return $this->errorResponse('Product no encontrado.', 404);
        }

        return $this->successResponse($product);
    }

    /**
     * POST /api/products
     * Crea un product.
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $dto = CreateProductRequest::toDTO($request->validated());

        return $this->successResponse($this->crearCasoUso->ejecutar($dto), 201);
    }

    /**
     * PUT/PATCH /api/products/{id}
     * Actualiza un product.
     */
    public function update(int $id, UpdateProductRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), ['id' => $id]);
        $dto = UpdateProductRequest::toDTO($data);

        try {
            return $this->successResponse($this->actualizarCasoUso->ejecutar($dto));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Product no encontrado.', 404);
        }
    }

    /**
     * PATCH /api/products/{id}/estado
     * Cambia el estado (activo/inactivo) de un product.
     */
    public function cambiarEstado(int $id, ChangeProductStatusRequest $request): JsonResponse
    {
        $estado = $request->validated()['estado'];

        $resultado = $this->cambiarEstadoCasoUso->ejecutar($id, $estado);

        if (!$resultado) {
            return $this->errorResponse('Product no encontrado.', 404);
        }

        return $this->successResponse('Estado actualizado correctamente.');
    }
}
