<?php

namespace App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Http\Controllers;

use App\Context\V1\Modules\EmissionPoints\Application\Adapters\EmissionPointSequentialServiceInterface;
use App\Context\V1\Modules\EmissionPoints\Application\DTOs\EmissionPointDTO;
use App\Context\V1\Modules\EmissionPoints\Application\UseCases\EmissionPointCrudService;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Http\Requests\CreateEmissionPointRequest;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Http\Requests\NextSequentialRequest;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Http\Requests\UpdateEmissionPointRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmissionPointController extends Controller
{
    public function __construct(
        private readonly EmissionPointCrudService $service,
        private readonly EmissionPointSequentialServiceInterface $sequentialService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'search' => $request->query('search'),
            'branch_office_id' => $request->query('branch_office_id'),
            'status' => $request->has('status') ? $request->boolean('status') : null,
            'default' => $request->has('default') ? $request->boolean('default') : null,
        ], static fn ($value) => $value !== null && $value !== '');

        $data = $this->service->list(
            max(1, (int) $request->query('page', 1)),
            min(100, max(1, (int) $request->query('perPage', 15))),
            $filters,
        );

        return response()->json([
            'status' => true,
            'response' => $data,
        ]);
    }

    public function nextSequential(NextSequentialRequest $request): JsonResponse
    {
        return $this->sequentialResponse($request, false);
    }

    private function sequentialResponse(NextSequentialRequest $request, bool $take): JsonResponse
    {
        $validated = $request->validated();
        $arguments = [
            (int) $validated['branch_office_id'],
            isset($validated['emission_point_id']) ? (int) $validated['emission_point_id'] : null,
            $validated['emission_point'] ?? null,
            isset($validated['carrier_id']) ? (int) $validated['carrier_id'] : null,
            $validated['document_code'],
        ];
        $result = $take
            ? $this->sequentialService->takeNextSequential(...$arguments)
            : $this->sequentialService->nextSequential(...$arguments);
        $data = $result->toArray();

        return response()->json([
            'status' => true,
            'response' => $data,
        ]);
    }

    public function takeNextSequential(NextSequentialRequest $request): JsonResponse
    {
        return $this->sequentialResponse($request, true);
    }

    public function show(int $id): JsonResponse
    {
        $point = $this->service->get($id);

        if (! $point) {
            $data = 'Emission point not found.';

            return response()->json([
                'status' => false,
                'response' => $data,
            ], 404);
        }

        $data = $point->toArray();

        return response()->json([
            'status' => true,
            'response' => $data,
        ]);
    }

    public function store(CreateEmissionPointRequest $request): JsonResponse
    {
        $data = $this->service->create(EmissionPointDTO::fromArray($request->validated()))->toArray();

        return response()->json([
            'status' => true,
            'response' => $data,
        ], 201);
    }

    public function update(int $id, UpdateEmissionPointRequest $request): JsonResponse
    {
        $data = $this->service->update(EmissionPointDTO::fromArray([...$request->validated(), 'id' => $id]))->toArray();

        return response()->json([
            'status' => true,
            'response' => $data,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->service->delete($id);

        if (! $deleted) {
            $data = 'Emission point not found.';

            return response()->json([
                'status' => false,
                'response' => $data,
            ], 404);
        }

        $data = 'Emission point deleted successfully.';

        return response()->json([
            'status' => true,
            'response' => $data,
        ]);
    }
}
