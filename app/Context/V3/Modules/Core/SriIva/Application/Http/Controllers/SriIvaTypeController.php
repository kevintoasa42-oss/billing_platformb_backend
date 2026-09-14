<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\Http\Controllers;

use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaPercentageCreateDTO;
use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaTypeCreateDTO;
use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaTypeUpdateDTO;
use App\Context\V3\Modules\Core\SriIva\Application\Http\Requests\SriIvaPercentageCreateRequest;
use App\Context\V3\Modules\Core\SriIva\Application\Http\Requests\SriIvaTypeCreateRequest;
use App\Context\V3\Modules\Core\SriIva\Application\Http\Requests\SriIvaTypeUpdateRequest;
use App\Context\V3\Modules\Core\SriIva\Application\UseCases\SriIvaPercentageUseCase;
use App\Context\V3\Modules\Core\SriIva\Application\UseCases\SriIvaTypeUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SriIvaTypeController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SriIvaTypeUseCase $typeUseCase,
        private readonly SriIvaPercentageUseCase $percentageUseCase,
    ) {}

    public function index(): JsonResponse
    {
        $types = $this->typeUseCase->all();

        return $this->success(
            array_map(fn ($type): array => $type->toArray(), $types),
            'Tipos de IVA cargados.',
        );
    }

    public function store(SriIvaTypeCreateRequest $request): JsonResponse
    {
        $dto = SriIvaTypeCreateDTO::fromArray($request->validated());
        $type = $this->typeUseCase->create($dto);

        return $this->success($type->toArray(), 'Tipo de IVA creado.', Response::HTTP_CREATED);
    }

    public function update(SriIvaTypeUpdateRequest $request, int $id): JsonResponse
    {
        $dto = SriIvaTypeUpdateDTO::fromArray($request->validated());
        $type = $this->typeUseCase->update($id, $dto);

        if ($type === null) {
            return $this->error('SRI IVA Type not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($type->toArray(), 'Tipo de IVA actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->typeUseCase->delete($id);

        if (! $deleted) {
            return $this->error('SRI IVA Type not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success(null, 'Tipo de IVA desactivado.');
    }

    public function percentages(int $type): JsonResponse
    {
        $percentages = $this->percentageUseCase->findByType($type);

        return $this->success(
            array_map(fn ($p): array => $p->toArray(), $percentages),
            'Porcentajes de IVA cargados.',
        );
    }

    public function storePercentage(SriIvaPercentageCreateRequest $request, int $type): JsonResponse
    {
        $dto = SriIvaPercentageCreateDTO::fromArray($request->validated(), $type);
        $percentage = $this->percentageUseCase->create($dto);

        return $this->success($percentage->toArray(), 'Porcentaje de IVA creado.', Response::HTTP_CREATED);
    }
}
