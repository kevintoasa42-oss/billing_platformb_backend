<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\IssuancePointUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Requests\IssuancePointUpdateRequest;
use App\Context\V3\Modules\Core\Establishment\Application\UseCases\EmissionPointUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IssuancePointController
{
    use ApiResponse;

    public function __construct(
        private readonly EmissionPointUseCase $useCase,
    ) {}

    public function update(IssuancePointUpdateRequest $request, int $id): JsonResponse
    {
        $dto = IssuancePointUpdateDTO::fromArray($request->validated());
        $point = $this->useCase->updateByLegacyId($id, $dto);

        if ($point === null) {
            return $this->error('No se encontró el punto de emisión solicitado.', Response::HTTP_NOT_FOUND, [
                'code' => 'issuance_point_not_found',
            ]);
        }

        return $this->success($point, 'Punto de emisión actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->useCase->deleteByLegacyId($id);

        if (! $deleted) {
            return $this->error('No se encontró el punto de emisión solicitado.', Response::HTTP_NOT_FOUND, [
                'code' => 'issuance_point_not_found',
            ]);
        }

        return $this->success(['deleted' => true], 'Punto de emisión desactivado.');
    }
}
