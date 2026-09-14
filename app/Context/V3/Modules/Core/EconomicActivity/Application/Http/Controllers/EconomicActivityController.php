<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Application\Http\Controllers;

use App\Context\V3\Modules\Core\EconomicActivity\Application\DTOs\EconomicActivityCreateDTO;
use App\Context\V3\Modules\Core\EconomicActivity\Application\DTOs\EconomicActivityUpdateDTO;
use App\Context\V3\Modules\Core\EconomicActivity\Application\Http\Requests\EconomicActivityCreateRequest;
use App\Context\V3\Modules\Core\EconomicActivity\Application\Http\Requests\EconomicActivityUpdateRequest;
use App\Context\V3\Modules\Core\EconomicActivity\Application\UseCases\EconomicActivityUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EconomicActivityController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EconomicActivityUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Economic activities loaded.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $activity = $this->useCase->find($id);

        if ($activity === null) {
            return $this->error('Economic activity not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($activity, 'Economic activity loaded.');
    }

    public function store(EconomicActivityCreateRequest $request): JsonResponse
    {
        $dto = EconomicActivityCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Economic activity created.', Response::HTTP_CREATED);
    }

    public function update(EconomicActivityUpdateRequest $request, string $id): JsonResponse
    {
        $activity = $this->useCase->update($id, EconomicActivityUpdateDTO::fromArray($request->validated()));

        if ($activity === null) {
            return $this->error('Economic activity not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($activity, 'Economic activity updated.');
    }
}
