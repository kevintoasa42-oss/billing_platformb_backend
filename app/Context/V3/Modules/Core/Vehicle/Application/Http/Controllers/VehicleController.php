<?php

namespace App\Context\V3\Modules\Core\Vehicle\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Vehicle\Application\DTOs\VehicleCreateDTO;
use App\Context\V3\Modules\Core\Vehicle\Application\DTOs\VehicleUpdateDTO;
use App\Context\V3\Modules\Core\Vehicle\Application\Http\Requests\VehicleCreateRequest;
use App\Context\V3\Modules\Core\Vehicle\Application\Http\Requests\VehicleUpdateRequest;
use App\Context\V3\Modules\Core\Vehicle\Application\UseCases\VehicleUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VehicleController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly VehicleUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Vehicles loaded.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $vehicle = $this->useCase->find($id);

        if ($vehicle === null) {
            return $this->error('Vehicle not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($vehicle, 'Vehicle loaded.');
    }

    public function store(VehicleCreateRequest $request): JsonResponse
    {
        $dto = VehicleCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Vehicle created.', Response::HTTP_CREATED);
    }

    public function update(VehicleUpdateRequest $request, string $id): JsonResponse
    {
        $vehicle = $this->useCase->update($id, VehicleUpdateDTO::fromArray($request->validated()));

        if ($vehicle === null) {
            return $this->error('Vehicle not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($vehicle, 'Vehicle updated.');
    }
}
