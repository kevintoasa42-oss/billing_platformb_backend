<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\CreateThirdPartyFieldDefinitionRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyFieldDefinitionIndexRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\UpdateThirdPartyFieldDefinitionRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyFieldDefinitionUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class ThirdPartyFieldDefinitionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ThirdPartyFieldDefinitionUseCase $useCase,
    ) {}

    public function index(ThirdPartyFieldDefinitionIndexRequest $request): JsonResponse
    {
        $data = array_map(
            static fn ($definition): array => $definition->toArray(),
            $this->useCase->all(
                $request->validated('scope'),
                $request->boolean('active_only', true),
            ),
        );

        return $this->success($data, 'Campos configurables cargados.');
    }

    public function store(CreateThirdPartyFieldDefinitionRequest $request): JsonResponse
    {
        $data = $this->useCase
            ->create(ThirdPartyFieldDefinitionCreateDTO::fromArray($request->validated()))
            ->toArray();

        return $this->success($data, 'Campo configurable creado.', 201);
    }

    public function update(UpdateThirdPartyFieldDefinitionRequest $request, string $definition): JsonResponse
    {
        $result = $this->useCase->update(
            $definition,
            ThirdPartyFieldDefinitionUpdateDTO::fromArray($request->validated()),
        );

        if ($result === null) {
            return $this->error('Definición de campo no encontrada.', 404, ['code' => 'third_party_field_not_found']);
        }

        return $this->success($result->toArray(), 'Campo configurable actualizado.');
    }

    public function destroy(string $definition): JsonResponse
    {
        $result = $this->useCase->deactivate($definition);

        if ($result === null) {
            return $this->error('Definición de campo no encontrada.', 404, ['code' => 'third_party_field_not_found']);
        }

        return $this->success($result->toArray(), 'Campo configurable desactivado.');
    }
}
