<?php

namespace App\Context\V1\EmissionPoints\Infrastructure\Laravel\Http\Controllers;

use App\Context\V1\EmissionPoints\Application\Adapters\EmissionPointSequentialServiceInterface;
use App\Context\V1\EmissionPoints\Application\DTOs\EmissionPointDTO;
use App\Context\V1\EmissionPoints\Application\UseCases\EmissionPointCrudService;
use App\Context\V1\EmissionPoints\Domain\Exceptions\EmissionPointNotFoundException;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Http\Requests\CreateEmissionPointRequest;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Http\Requests\NextSequentialRequest;
use App\Context\V1\EmissionPoints\Infrastructure\Laravel\Http\Requests\UpdateEmissionPointRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmissionPointController extends Controller
{
    use ApiResponse;

    public function __construct(
        private EmissionPointCrudService $service,
        private EmissionPointSequentialServiceInterface $sequentialService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'branch_office_id' => $request->query('branch_office_id'),
            'status' => $request->has('status') ? $request->boolean('status') : null,
            'default' => $request->has('default') ? $request->boolean('default') : null,
        ], static fn ($value) => $value !== null && $value !== '');

        return $this->successResponse($this->service->list(max(1, (int) $request->query('page', 1)), min(100, max(1, (int) $request->query('perPage', 15))), $filters));
    }

    public function nextSequential(NextSequentialRequest $request): JsonResponse
    {
        return $this->sequentialResponse($request, false);
    }

    public function takeNextSequential(NextSequentialRequest $request): JsonResponse
    {
        return $this->sequentialResponse($request, true);
    }

    private function sequentialResponse(NextSequentialRequest $request, bool $take): JsonResponse
    {
        $data = $request->validated();

        try {
            $arguments = [
                (int) $data['branch_office_id'],
                isset($data['emission_point_id']) ? (int) $data['emission_point_id'] : null,
                $data['emission_point'] ?? null,
            ];
            $result = $take
                ? $this->sequentialService->takeNextSequential(...$arguments)
                : $this->sequentialService->nextSequential(...$arguments);

            return $this->successResponse($result->toArray());
        } catch (EmissionPointNotFoundException) {
            return $this->errorResponse('Emission point not found for the supplied branch office.', 404);
        }
    }

    public function show(int $id): JsonResponse
    {
        $point = $this->service->get($id);

        return $point ? $this->successResponse($point->toArray()) : $this->errorResponse('Emission point not found.', 404);
    }

    public function store(CreateEmissionPointRequest $request): JsonResponse
    {
        return $this->successResponse($this->service->create(EmissionPointDTO::fromArray($request->validated()))->toArray(), 201);
    }

    public function update(int $id, UpdateEmissionPointRequest $request): JsonResponse
    {
        try {
            return $this->successResponse($this->service->update(EmissionPointDTO::fromArray([...$request->validated(), 'id' => $id]))->toArray());
        } catch (EmissionPointNotFoundException) {
            return $this->errorResponse('Emission point not found.', 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->service->delete($id)
            ? $this->successResponse('Emission point deleted successfully.')
            : $this->errorResponse('Emission point not found.', 404);
    }
}
