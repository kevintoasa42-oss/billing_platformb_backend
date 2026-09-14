<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\Http\Controllers;

use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaPercentageUpdateDTO;
use App\Context\V3\Modules\Core\SriIva\Application\Http\Requests\SriIvaPercentageUpdateRequest;
use App\Context\V3\Modules\Core\SriIva\Application\UseCases\SriIvaPercentageUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SriIvaPercentageController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly SriIvaPercentageUseCase $percentageUseCase,
    ) {}

    public function update(SriIvaPercentageUpdateRequest $request, int $id): JsonResponse
    {
        $dto = SriIvaPercentageUpdateDTO::fromArray($request->validated());
        $percentage = $this->percentageUseCase->update($id, $dto);

        if ($percentage === null) {
            return $this->error('SRI IVA Percentage not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success($percentage->toArray(), 'Porcentaje de IVA actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->percentageUseCase->delete($id);

        if (! $deleted) {
            return $this->error('SRI IVA Percentage not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->success(null, 'Porcentaje de IVA desactivado.');
    }
}
