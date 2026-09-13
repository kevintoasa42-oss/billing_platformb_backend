<?php

namespace App\Context\V1\Partners\Infrastructure\Laravel\Http\Controllers;

use App\Context\V1\Partners\Application\DTOs\PartnerDTO;
use App\Context\V1\Partners\Application\UseCases\PartnerCrudService;
use App\Context\V1\Partners\Domain\Exceptions\PartnerNotFoundException;
use App\Context\V1\Partners\Infrastructure\Laravel\Http\Requests\CreatePartnerRequest;
use App\Context\V1\Partners\Infrastructure\Laravel\Http\Requests\UpdatePartnerRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PartnerCrudService $service) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'identification_type' => $request->query('identification_type'),
            'status' => $request->has('status') ? $request->boolean('status') : null,
        ], static fn ($value) => $value !== null && $value !== '');

        return $this->successResponse($this->service->list(
            max(1, (int) $request->query('page', 1)),
            min(100, max(1, (int) $request->query('perPage', 15))),
            $filters,
        ));
    }

    public function show(int $id): JsonResponse
    {
        $partner = $this->service->get($id);

        return $partner
            ? $this->successResponse($partner->toArray())
            : $this->errorResponse('Partner not found.', 404);
    }

    public function store(CreatePartnerRequest $request): JsonResponse
    {
        return $this->successResponse($this->service->create(PartnerDTO::fromArray($request->validated()))->toArray(), 201);
    }

    public function update(int $id, UpdatePartnerRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->update(PartnerDTO::fromArray([
                ...$request->validated(),
                'id' => $id,
            ]))->toArray());
        } catch (PartnerNotFoundException) {
            return $this->errorResponse('Partner not found.', 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id)
            ? $this->successResponse('Partner deleted successfully.')
            : $this->errorResponse('Partner not found.', 404);
    }
}
