<?php

namespace App\Context\V3\Modules\Core\Settings\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Settings\Application\DTOs\AdditionalInfoPresetCreateDTO;
use App\Context\V3\Modules\Core\Settings\Application\DTOs\AdditionalInfoPresetUpdateDTO;
use App\Context\V3\Modules\Core\Settings\Application\Http\Requests\AdditionalInfoPresetCreateRequest;
use App\Context\V3\Modules\Core\Settings\Application\Http\Requests\AdditionalInfoPresetUpdateRequest;
use App\Context\V3\Modules\Core\Settings\Application\UseCases\AdditionalInfoPresetUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AdditionalInfoPresetController
{
    use ApiResponse;

    public function __construct(
        private readonly AdditionalInfoPresetUseCase $useCase,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Datos adicionales cargados.');
    }

    public function available(): JsonResponse
    {
        return $this->success($this->useCase->all(true), 'Datos adicionales disponibles.');
    }

    public function store(AdditionalInfoPresetCreateRequest $request): JsonResponse
    {
        $dto = AdditionalInfoPresetCreateDTO::fromArray($request->validated());

        return $this->success($this->useCase->create($dto), 'Dato adicional creado.', Response::HTTP_CREATED);
    }

    public function update(AdditionalInfoPresetUpdateRequest $request, int $id): JsonResponse
    {
        $dto = AdditionalInfoPresetUpdateDTO::fromArray($request->validated());
        $preset = $this->useCase->update($id, $dto);

        if ($preset === null) {
            return $this->error('No se encontró el dato adicional.', Response::HTTP_NOT_FOUND, [
                'code' => 'additional_info_preset_not_found',
            ]);
        }

        return $this->success($preset, 'Dato adicional actualizado.');
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->useCase->delete($id);

        if (! $deleted) {
            return $this->error('No se encontró el dato adicional.', Response::HTTP_NOT_FOUND, [
                'code' => 'additional_info_preset_not_found',
            ]);
        }

        return $this->success(['deleted' => true], 'Dato adicional desactivado.');
    }
}
