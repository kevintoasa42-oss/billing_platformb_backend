<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyCreateRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyFieldDefinitionRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyFieldValuesRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyUpdateRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\CreateThirdPartyUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyAvailabilityUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyFieldDefinitionUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class ThirdPartyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ThirdPartyUseCase $useCase,
        private readonly CreateThirdPartyUseCase $createUseCase,
        private readonly ThirdPartyAvailabilityUseCase $availabilityUseCase,
        private readonly ThirdPartyFieldDefinitionUseCase $fieldDefinitionUseCase,
        private readonly ThirdPartyFieldRepositoryInterface $fieldRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success(
            $this->useCase->all($this->tenantId($request)),
            'Terceros cargados.',
        );
    }

    public function customers(Request $request): JsonResponse
    {
        $role = $request->query('role');

        return $this->success(
            $this->useCase->customers($this->tenantId($request), is_string($role) ? $role : null),
            'Clientes cargados.',
        );
    }

    public function roles(): JsonResponse
    {
        return $this->success($this->useCase->roles(), 'Roles cargados.');
    }

    public function carriers(Request $request): JsonResponse
    {
        return $this->success(
            $this->useCase->carriers($this->tenantId($request)),
            'Transportistas cargados.',
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $thirdParty = $this->useCase->find($this->tenantId($request), $id);

        if ($thirdParty === null) {
            return $this->error('Tercero no encontrado.', Response::HTTP_NOT_FOUND, [
                'code' => 'third_party_not_found',
            ]);
        }

        return $this->success($thirdParty, 'Tercero cargado.');
    }

    public function showCustomer(Request $request, string $id): JsonResponse
    {
        $customer = $this->useCase->findCustomer($this->tenantId($request), $id);

        if ($customer === null) {
            return $this->error('Cliente no encontrado.', Response::HTTP_NOT_FOUND, [
                'code' => 'customer_not_found',
            ]);
        }

        return $this->success($customer, 'Cliente cargado.');
    }

    public function store(ThirdPartyCreateRequest $request): JsonResponse
    {
        $dto = ThirdPartyCreateDTO::fromArray([
            ...$request->validated(),
            'tenant_id' => $this->tenantId($request),
        ]);

        try {
            $thirdParty = $this->createUseCase->create($dto);
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success($thirdParty, 'Tercero creado.', Response::HTTP_CREATED);
    }

    public function update(ThirdPartyUpdateRequest $request, string $id): JsonResponse
    {
        $dto = ThirdPartyUpdateDTO::fromArray($request->validated());

        try {
            $thirdParty = $this->useCase->update($this->tenantId($request), $id, $dto);
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        if ($thirdParty === null) {
            return $this->error('Tercero no encontrado.', Response::HTTP_NOT_FOUND, [
                'code' => 'third_party_not_found',
            ]);
        }

        return $this->success($thirdParty, 'Tercero actualizado.');
    }

    public function availability(Request $request): JsonResponse
    {
        $identification = (string) $request->query('identification', $request->query('identification_number', ''));
        $type = $request->query('identification_type');

        try {
            $result = $this->availabilityUseCase->execute(
                $identification,
                is_string($type) ? $type : null,
                is_string($request->query('exclude_id')) ? $request->query('exclude_id') : null,
            );
        } catch (InvalidArgumentException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'invalid_identification']);
        }

        return $this->success($result, 'Disponibilidad de identificación verificada.');
    }

    public function fieldDefinitions(Request $request): JsonResponse
    {
        $scope = $request->query('scope');
        $scope = is_string($scope) ? strtolower(trim($scope)) : null;

        try {
            $definitions = $this->fieldDefinitionUseCase->all($scope, true);
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success(
            array_map(fn ($definition) => $this->mapDefinition($definition), $definitions),
            'Campos configurables cargados.',
        );
    }

    public function createFieldDefinition(ThirdPartyFieldDefinitionRequest $request): JsonResponse
    {
        try {
            $definition = $this->fieldDefinitionUseCase->create(
                \App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionCreateDTO::fromArray($request->validated()),
            );
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success($this->mapDefinition($definition), 'Campo configurable creado.', Response::HTTP_CREATED);
    }

    public function updateFieldDefinition(ThirdPartyFieldDefinitionRequest $request, string $definition): JsonResponse
    {
        try {
            $updated = $this->fieldDefinitionUseCase->update(
                $definition,
                \App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionUpdateDTO::fromArray($request->validated()),
            );
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }
        if ($updated === null) {
            return $this->error('Definición de campo no encontrada.', Response::HTTP_NOT_FOUND, ['code' => 'third_party_field_not_found']);
        }

        return $this->success($this->mapDefinition($updated), 'Campo configurable actualizado.');
    }

    public function deactivateFieldDefinition(Request $request, string $definition): JsonResponse
    {
        try {
            $deactivated = $this->fieldDefinitionUseCase->deactivate($definition);
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }
        if ($deactivated === null) {
            return $this->error('Definición de campo no encontrada.', Response::HTTP_NOT_FOUND, ['code' => 'third_party_field_not_found']);
        }

        return $this->success($this->mapDefinition($deactivated), 'Campo configurable desactivado.');
    }

    public function fields(Request $request, string $id): JsonResponse
    {
        try {
            $values = $this->fieldRepository->values($this->tenantId($request), $id);
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success(['third_party_id' => $id, 'custom_fields' => $values], 'Campos del tercero cargados.');
    }

    public function updateFields(ThirdPartyFieldValuesRequest $request, string $id): JsonResponse
    {
        try {
            $values = $this->fieldRepository->replaceValues(
                $this->tenantId($request),
                $id,
                (array) $request->validated('custom_fields'),
                $this->userId($request),
            );
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success(['third_party_id' => $id, 'custom_fields' => $values], 'Campos del tercero actualizados.');
    }

    /** @return array<string, mixed> */
    private function mapDefinition(object $definition): array
    {
        return [
            'id' => $definition->id,
            'code' => $definition->code,
            'label' => $definition->label,
            'scope' => $definition->scope,
            'data_type' => $definition->dataType,
            'validation' => $definition->validation,
            'sort_order' => $definition->sortOrder,
            'is_required' => $definition->isRequired,
            'is_active' => $definition->isActive,
        ];
    }

    private function tenantId(Request $request): string
    {
        $tenantId = $request->attributes->get('v3.tenant_id');

        if (! is_string($tenantId) || $tenantId === '') {
            throw new DomainException('El contexto tenant es obligatorio.');
        }

        return $tenantId;
    }

    private function userId(Request $request): ?string
    {
        $userId = $request->attributes->get('v3.user_id');

        return is_string($userId) && $userId !== '' ? $userId : null;
    }
}
