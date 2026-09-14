<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EmissionPointCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EmissionPointUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\EmissionPointCreateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\EmissionPointUpdateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\UseCases\EmissionPointUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EmissionPointController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EmissionPointUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Emission points loaded.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $emissionPoint = $this->useCase->find($id);

        if ($emissionPoint === null) {
            return $this->error('Emission point not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($emissionPoint, 'Emission point loaded.');
    }

    public function byEstablishment(Request $request, string $establishmentId): JsonResponse
    {
        return $this->success($this->useCase->byEstablishment($establishmentId), 'Emission points loaded.');
    }

    public function store(EmissionPointCreateRequest $request): JsonResponse
    {
        $dto = EmissionPointCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Emission point created.', Response::HTTP_CREATED);
    }

    public function update(EmissionPointUpdateRequest $request, string $id): JsonResponse
    {
        $emissionPoint = $this->useCase->update($id, EmissionPointUpdateDTO::fromArray($request->validated()));

        if ($emissionPoint === null) {
            return $this->error('Emission point not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($emissionPoint, 'Emission point updated.');
    }
}
