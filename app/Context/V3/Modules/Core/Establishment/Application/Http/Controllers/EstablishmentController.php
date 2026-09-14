<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EstablishmentCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EstablishmentUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\EstablishmentCreateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\EstablishmentUpdateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\UseCases\EstablishmentUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EstablishmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly EstablishmentUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Establishments loaded.');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $establishment = $this->useCase->find($id);

        if ($establishment === null) {
            return $this->error('Establishment not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($establishment, 'Establishment loaded.');
    }

    public function store(EstablishmentCreateRequest $request): JsonResponse
    {
        $dto = EstablishmentCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Establishment created.', Response::HTTP_CREATED);
    }

    public function update(EstablishmentUpdateRequest $request, string $id): JsonResponse
    {
        $establishment = $this->useCase->update($id, EstablishmentUpdateDTO::fromArray($request->validated()));

        if ($establishment === null) {
            return $this->error('Establishment not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($establishment, 'Establishment updated.');
    }
}
