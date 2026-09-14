<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierEmissionPointCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierEmissionPointCreateRequest;
use App\Context\V3\Modules\Core\Carrier\Application\UseCases\CarrierEmissionPointUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CarrierEmissionPointController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CarrierEmissionPointUseCase $useCase,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Carrier emission points loaded.');
    }

    public function store(CarrierEmissionPointCreateRequest $request): JsonResponse
    {
        $dto = CarrierEmissionPointCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Carrier emission point created.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->useCase->find($id);

        if ($result === null) {
            return $this->error('Carrier emission point not found.', 404);
        }

        return $this->success($result, 'Carrier emission point loaded.');
    }

    public function byEstablishment(string $establishmentId): JsonResponse
    {
        return $this->success($this->useCase->byEstablishment($establishmentId), 'Carrier emission points loaded.');
    }
}
