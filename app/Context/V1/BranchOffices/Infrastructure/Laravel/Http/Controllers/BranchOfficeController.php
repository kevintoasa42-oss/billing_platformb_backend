<?php

namespace App\Context\V1\BranchOffices\Infrastructure\Laravel\Http\Controllers;

use App\Context\V1\BranchOffices\Application\DTOs\BranchOfficeDTO;
use App\Context\V1\BranchOffices\Application\UseCases\BranchOfficeCrudService;
use App\Context\V1\BranchOffices\Domain\Exceptions\BranchOfficeNotFoundException;
use App\Context\V1\BranchOffices\Infrastructure\Laravel\Http\Requests\CreateBranchOfficeRequest;
use App\Context\V1\BranchOffices\Infrastructure\Laravel\Http\Requests\UpdateBranchOfficeRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BranchOfficeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly BranchOfficeCrudService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'status' => $request->has('status') ? $request->boolean('status') : null,
            'default' => $request->has('default') ? $request->boolean('default') : null,
        ], static fn ($value) => $value !== null && $value !== '');

        return $this->successResponse($this->service->list(max(1, (int) $request->query('page', 1)), min(100, max(1, (int) $request->query('perPage', 15))), $filters));
    }

    public function show(int $id): JsonResponse
    {
        $branchOffice = $this->service->get($id);

        return $branchOffice ? $this->successResponse($branchOffice->toArray()) : $this->errorResponse('Branch office not found.', 404);
    }

    public function store(CreateBranchOfficeRequest $request): JsonResponse
    {
        return $this->successResponse($this->service->create(BranchOfficeDTO::fromArray($request->validated()))->toArray(), 201);
    }

    public function update(int $id, UpdateBranchOfficeRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->update(BranchOfficeDTO::fromArray([...$request->validated(), 'id' => $id]))->toArray());
        } catch (BranchOfficeNotFoundException) {
            return $this->errorResponse('Branch office not found.', 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id)
            ? $this->successResponse('Branch office deleted successfully.')
            : $this->errorResponse('Branch office not found.', 404);
    }
}
