<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyCreateRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyFieldDefinitionRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyFieldValuesRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Requests\ThirdPartyUpdateRequest;
use App\Context\V3\Modules\Core\ThirdParty\Application\UseCases\ThirdPartyUseCase;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ThirdPartyController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ThirdPartyUseCase $useCase,
        private readonly ThirdPartyFieldRepositoryInterface $fieldRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->useCase->all(), 'Terceros cargados.');
    }

    public function roles(Request $request): JsonResponse
    {
        return $this->success($this->useCase->roles(), 'Roles cargados.');
    }

    public function availability(Request $request): JsonResponse
    {
        $identification = (string) $request->query('identification', $request->query('identification_number', ''));
        $type = $request->query('identification_type');
        $excludeId = $request->query('exclude_id');

        try {
            $result = $this->useCase->identificationAvailability(
                $identification,
                is_string($type) ? $type : null,
                is_string($excludeId) ? $excludeId : null,
            );
        } catch (\InvalidArgumentException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'invalid_identification']);
        }

        return $this->success($result, 'Disponibilidad de identificación verificada.');
    }

    public function fieldDefinitions(Request $request): JsonResponse
    {
        $scope = $request->query('scope');
        $scope = is_string($scope) ? strtolower(trim($scope)) : null;

        try {
            $definitions = $this->fieldRepository->definitions(
                $this->tenantId($request),
                $scope,
                $request->boolean('active_only', true),
            );
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success($definitions, 'Campos configurables cargados.');
    }

    public function createFieldDefinition(ThirdPartyFieldDefinitionRequest $request): JsonResponse
    {
        try {
            $definition = $this->fieldRepository->createDefinition(
                $this->tenantId($request),
                $request->validated(),
                $this->userId($request),
            );
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success($definition, 'Campo configurable creado.', Response::HTTP_CREATED);
    }

    public function updateFieldDefinition(ThirdPartyFieldDefinitionRequest $request, string $definition): JsonResponse
    {
        try {
            $updated = $this->fieldRepository->updateDefinition(
                $this->tenantId($request),
                $definition,
                $request->validated(),
                $this->userId($request),
            );
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        if ($updated === null) {
            return $this->error('Definición de campo no encontrada.', Response::HTTP_NOT_FOUND, ['code' => 'third_party_field_not_found']);
        }

        return $this->success($updated, 'Campo configurable actualizado.');
    }

    public function deactivateFieldDefinition(Request $request, string $definition): JsonResponse
    {
        try {
            $updated = $this->fieldRepository->deactivateDefinition(
                $this->tenantId($request),
                $definition,
                $this->userId($request),
            );
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        if ($updated === null) {
            return $this->error('Definición de campo no encontrada.', Response::HTTP_NOT_FOUND, ['code' => 'third_party_field_not_found']);
        }

        return $this->success($updated, 'Campo configurable desactivado.');
    }

    public function customers(Request $request): JsonResponse
    {
        $role = $request->query('role');

        return $this->success($this->useCase->customers(is_string($role) ? $role : null), 'Clientes cargados.');
    }

    public function carriers(Request $request): JsonResponse
    {
        return $this->success($this->useCase->carriers(), 'Transportistas cargados.');
    }

    public function store(ThirdPartyCreateRequest $request): JsonResponse
    {
        $dto = ThirdPartyCreateDTO::fromArray($request->validated());

        try {
            $thirdParty = $this->useCase->create($dto);
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        return $this->success($thirdParty, 'Tercero creado.', Response::HTTP_CREATED);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $thirdParty = $this->useCase->find($id);

        if ($thirdParty === null) {
            return $this->error('Tercero no encontrado.', Response::HTTP_NOT_FOUND, ['code' => 'third_party_not_found']);
        }

        return $this->success($thirdParty, 'Tercero cargado.');
    }

    public function showCustomer(Request $request, string $id): JsonResponse
    {
        $customer = $this->useCase->findCustomer($id);

        if ($customer === null) {
            return $this->error('Cliente no encontrado.', Response::HTTP_NOT_FOUND, ['code' => 'customer_not_found']);
        }

        return $this->success($customer, 'Cliente cargado.');
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

    public function update(ThirdPartyUpdateRequest $request, string $id): JsonResponse
    {
        $dto = ThirdPartyUpdateDTO::fromArray($request->validated());

        try {
            $thirdParty = $this->useCase->update($id, $dto);
        } catch (DomainException $error) {
            return $this->error($error->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY, ['code' => 'third_party_fields_invalid']);
        }

        if ($thirdParty === null) {
            return $this->error('Tercero no encontrado.', Response::HTTP_NOT_FOUND, ['code' => 'third_party_not_found']);
        }

        return $this->success($thirdParty, 'Tercero actualizado.');
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
