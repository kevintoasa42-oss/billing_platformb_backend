<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\BranchCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\BranchUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\IssuancePointCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\BranchCreateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\BranchUpdateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\IssuancePointCreateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\UseCases\EmissionPointUseCase;
use App\Context\V3\Modules\Core\Establishment\Application\UseCases\EstablishmentUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class BranchController
{
    use ApiResponse;

    public function __construct(
        private readonly EstablishmentUseCase $useCase,
        private readonly EmissionPointUseCase $pointUseCase,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->useCase->allBranches(), 'Sucursales cargadas.');
    }

    public function store(BranchCreateRequest $request): JsonResponse
    {
        $dto = BranchCreateDTO::fromArray($request->validated());
        $branch = $this->useCase->createBranch($dto);

        if ($branch === null) {
            return $this->error('No se pudo crear la sucursal.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->success($branch, 'Sucursal creada.', Response::HTTP_CREATED);
    }

    public function update(BranchUpdateRequest $request, int $id): JsonResponse
    {
        $dto = BranchUpdateDTO::fromArray($request->validated());
        $branch = $this->useCase->updateBranch($id, $dto);

        if ($branch === null) {
            return $this->error('No se encontró la sucursal solicitada.', Response::HTTP_NOT_FOUND, [
                'code' => 'branch_not_found',
            ]);
        }

        return $this->success($branch, 'Sucursal actualizada.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->useCase->deleteBranch($id);

        if (! $deleted) {
            return $this->error('No se encontró la sucursal solicitada.', Response::HTTP_NOT_FOUND, [
                'code' => 'branch_not_found',
            ]);
        }

        return $this->success(['deleted' => true], 'Sucursal desactivada.');
    }

    public function issuancePoints(int $branch): JsonResponse
    {
        $points = $this->pointUseCase->byBranchLegacyId($branch);

        return $this->success($points, 'Puntos de emisión cargados.');
    }

    public function createIssuancePoint(IssuancePointCreateRequest $request, int $branch): JsonResponse
    {
        $dto = IssuancePointCreateDTO::fromArray($request->validated());
        $point = $this->pointUseCase->createForBranch($branch, $dto);

        if ($point === null) {
            return $this->error('No se encontró la sucursal solicitada.', Response::HTTP_NOT_FOUND, [
                'code' => 'branch_not_found',
            ]);
        }

        return $this->success($point, 'Punto de emisión creado.', Response::HTTP_CREATED);
    }

    public function nextSequential(int $branch, int $point): JsonResponse
    {
        $result = $this->pointUseCase->nextSequential($branch, $point);

        if ($result === null) {
            return $this->error('No se encontró el punto de emisión solicitado.', Response::HTTP_NOT_FOUND, [
                'code' => 'issuance_point_not_found',
            ]);
        }

        return $this->success($result, 'Secuencial consultado.');
    }
}
