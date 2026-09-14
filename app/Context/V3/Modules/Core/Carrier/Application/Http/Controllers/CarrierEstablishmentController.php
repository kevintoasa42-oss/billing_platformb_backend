<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierEstablishmentCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierEstablishmentCreateRequest;
use App\Context\V3\Modules\Core\Carrier\Application\UseCases\CarrierEstablishmentUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CarrierEstablishmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CarrierEstablishmentUseCase $useCase,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Carrier establishments loaded.');
    }

    public function store(CarrierEstablishmentCreateRequest $request): JsonResponse
    {
        $dto = CarrierEstablishmentCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Carrier establishment created.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->useCase->find($id);

        if ($result === null) {
            return $this->error('Carrier establishment not found.', 404);
        }

        return $this->success($result, 'Carrier establishment loaded.');
    }
}
