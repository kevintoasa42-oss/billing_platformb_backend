<?php

namespace App\Contexto\Product\Aplicacion\Http\Controllers;

use App\Contexto\Product\Aplicacion\CasosDeUso\ActualizarProductoCasoUso;
use App\Contexto\Product\Aplicacion\CasosDeUso\CambiarEstadoProductoCasoUso;
use App\Contexto\Product\Aplicacion\CasosDeUso\CrearProductoCasoUso;
use App\Contexto\Product\Aplicacion\CasosDeUso\ListarProductosCasoUso;
use App\Contexto\Product\Aplicacion\CasosDeUso\ObtenerProductoPorIdCasoUso;
use App\Contexto\Product\Aplicacion\Http\Requests\ActualizarProductoRequest;
use App\Contexto\Product\Aplicacion\Http\Requests\CambiarEstadoProductoRequest;
use App\Contexto\Product\Aplicacion\Http\Requests\CrearProductoRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CrearProductoCasoUso $crearCasoUso,
        private ListarProductosCasoUso $listarCasoUso,
        private ObtenerProductoPorIdCasoUso $obtenerPorIdCasoUso,
        private ActualizarProductoCasoUso $actualizarCasoUso,
        private CambiarEstadoProductoCasoUso $cambiarEstadoCasoUso,
    ) {}

    /**
     * GET /api/productos?page=1&perPage=15&search=...
     * Lista paginada de productos.
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
     * GET /api/productos/{id}
     * Obtiene un producto por ID.
     */
    public function show(int $id): JsonResponse
    {
        $producto = $this->obtenerPorIdCasoUso->ejecutar($id);

        if (!$producto) {
            return $this->errorResponse('Producto no encontrado.', 404);
        }

        return $this->successResponse($producto);
    }

    /**
     * POST /api/productos
     * Crea un producto.
     */
    public function store(CrearProductoRequest $request): JsonResponse
    {
        $dto = CrearProductoRequest::toDTO($request->validated());

        return $this->successResponse($this->crearCasoUso->ejecutar($dto), 201);
    }

    /**
     * PUT/PATCH /api/productos/{id}
     * Actualiza un producto.
     */
    public function update(int $id, ActualizarProductoRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), ['id' => $id]);
        $dto = ActualizarProductoRequest::toDTO($data);

        try {
            return $this->successResponse($this->actualizarCasoUso->ejecutar($dto));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Producto no encontrado.', 404);
        }
    }

    /**
     * PATCH /api/productos/{id}/estado
     * Cambia el estado (activo/inactivo) de un producto.
     */
    public function cambiarEstado(int $id, CambiarEstadoProductoRequest $request): JsonResponse
    {
        $estado = $request->validated()['estado'];

        $resultado = $this->cambiarEstadoCasoUso->ejecutar($id, $estado);

        if (!$resultado) {
            return $this->errorResponse('Producto no encontrado.', 404);
        }

        return $this->successResponse('Estado actualizado correctamente.');
    }
}
