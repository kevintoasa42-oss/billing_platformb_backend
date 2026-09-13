<?php

namespace App\Context\V1\Carrier\Application\Http\Controllers;

use App\Context\V1\Carrier\Application\UseCases\UpdateCarrierUseCase;
use App\Context\V1\Carrier\Application\UseCases\ChangeCarrierStatusUseCase;
use App\Context\V1\Carrier\Application\UseCases\CreateCarrierUseCase;
use App\Context\V1\Carrier\Application\UseCases\ListCarriersUseCase;
use App\Context\V1\Carrier\Application\UseCases\GetCarrierByIdUseCase;
use App\Context\V1\Carrier\Application\Http\Requests\UpdateCarrierRequest;
use App\Context\V1\Carrier\Application\Http\Requests\ChangeCarrierStatusRequest;
use App\Context\V1\Carrier\Application\Http\Requests\CreateCarrierRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateCarrierUseCase $createUseCase,
        private ListCarriersUseCase $listUseCase,
        private GetCarrierByIdUseCase $getByIdUseCase,
        private UpdateCarrierUseCase $updateUseCase,
        private ChangeCarrierStatusUseCase $changeStatusUseCase,
    ) {}

    /**
     * GET /api/carriers?page=1&perPage=15&search=...
     * Paginated list of carriers.
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
     * GET /api/carriers/{id}
     * Get a carrier by ID.
     */
    public function show(int $id): JsonResponse
    {
        $carrier = $this->getByIdUseCase->execute($id);

        if (!$carrier) {
            return $this->errorResponse('Carrier not found.', 404);
        }

        return $this->successResponse($carrier);
    }

    /**
     * POST /api/carriers
     * Create a carrier.
     */
    public function store(CreateCarrierRequest $request): JsonResponse
    {
        $dto = CreateCarrierRequest::toDTO($request->validated());

        return $this->successResponse($this->createUseCase->execute($dto), 201);
    }

    /**
     * PUT/PATCH /api/carriers/{id}
     * Update a carrier.
     */
    public function update(int $id, UpdateCarrierRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), ['id' => $id]);
        $dto = UpdateCarrierRequest::toDTO($data);

        try {
            return $this->successResponse($this->updateUseCase->execute($dto));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Carrier not found.', 404);
        }
    }

    /**
     * PATCH /api/carriers/{id}/status
     * Change the status (active/inactive) of a carrier.
     */
    public function changeStatus(int $id, ChangeCarrierStatusRequest $request): JsonResponse
    {
        $status = $request->validated()['status'];

        $result = $this->changeStatusUseCase->execute($id, $status);

        if (!$result) {
            return $this->errorResponse('Carrier not found.', 404);
        }

        return $this->successResponse('Status updated successfully.');
    }
}
