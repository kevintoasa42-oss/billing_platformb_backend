<?php

namespace App\Context\V3\Modules\Core\Product\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Product\Application\Http\Requests\ProductSettingsUpdateRequest;
use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductSettingsDTO;
use App\Context\V3\Modules\Core\Product\Application\UseCases\ProductSettingsUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class ProductSettingsController
{
    use ApiResponse;

    public function __construct(
        private readonly ProductSettingsUseCase $useCase,
    ) {}

    public function index(): JsonResponse
    {
        $settings = $this->useCase->get();

        return $this->success($settings->toArray(), 'Configuración de productos cargada.');
    }

    public function update(ProductSettingsUpdateRequest $request): JsonResponse
    {
        $dto = ProductSettingsDTO::fromArray($request->validated());
        $settings = $this->useCase->update($dto);

        return $this->success($settings->toArray(), 'Configuración de productos guardada.');
    }
}
