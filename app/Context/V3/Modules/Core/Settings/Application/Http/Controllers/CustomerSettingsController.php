<?php

namespace App\Context\V3\Modules\Core\Settings\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Settings\Application\DTOs\CustomerSettingsDTO;
use App\Context\V3\Modules\Core\Settings\Application\Http\Requests\CustomerSettingsUpdateRequest;
use App\Context\V3\Modules\Core\Settings\Application\UseCases\CustomerSettingsUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class CustomerSettingsController
{
    use ApiResponse;

    public function __construct(
        private readonly CustomerSettingsUseCase $useCase,
    ) {}

    public function update(CustomerSettingsUpdateRequest $request): JsonResponse
    {
        $dto = CustomerSettingsDTO::fromArray($request->validated());

        return $this->success($this->useCase->update($dto), 'Configuración de clientes guardada.');
    }
}
