<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierAffiliationCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierAffiliationUpdateDTO;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierAffiliationCreateRequest;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierAffiliationUpdateRequest;
use App\Context\V3\Modules\Core\Carrier\Application\UseCases\CarrierAffiliationUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class CarrierAffiliationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CarrierAffiliationUseCase $useCase,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Carrier affiliations loaded.');
    }

    public function store(CarrierAffiliationCreateRequest $request): JsonResponse
    {
        $dto = CarrierAffiliationCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Carrier affiliation created.', 201);
    }

    public function show(string $id): JsonResponse
    {
        $result = $this->useCase->find($id);

        if ($result === null) {
            return $this->error('Carrier affiliation not found.', 404);
        }

        return $this->success($result, 'Carrier affiliation loaded.');
    }

    public function update(CarrierAffiliationUpdateRequest $request, string $id): JsonResponse
    {
        $dto = CarrierAffiliationUpdateDTO::fromArray($request->validated());

        $result = $this->useCase->update($id, $dto);

        if ($result === null) {
            return $this->error('Carrier affiliation not found.', 404);
        }

        return $this->success($result, 'Carrier affiliation updated.');
    }
}
