<?php

namespace App\Context\V3\Modules\Core\Settings\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Settings\Application\DTOs\PaymentMethodUpdateDTO;
use App\Context\V3\Modules\Core\Settings\Application\Http\Requests\PaymentMethodUpdateRequest;
use App\Context\V3\Modules\Core\Settings\Application\UseCases\PaymentMethodSettingsUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class PaymentMethodSettingsController
{
    use ApiResponse;

    public function __construct(
        private readonly PaymentMethodSettingsUseCase $useCase,
    ) {}

    public function index(): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Métodos de pago cargados.');
    }

    public function update(PaymentMethodUpdateRequest $request, string $code): JsonResponse
    {
        try {
            $dto = PaymentMethodUpdateDTO::fromArray($request->validated());

            return $this->success($this->useCase->update($code, $dto), 'Método de pago actualizado.');
        } catch (\DomainException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND, [
                'code' => 'payment_method_not_found',
            ]);
        }
    }
}
