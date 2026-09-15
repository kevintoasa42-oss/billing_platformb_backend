<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyQueryRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;

/**
 * Read-side orchestration for the ThirdParty directory.
 *
 * Creation, availability, and field definitions live in their own focused
 * use cases; this class coordinates the remaining read/update operations
 * while preserving the reference API response shapes.
 */
final class ThirdPartyUseCase
{
    public function __construct(
        private readonly ThirdPartyRepositoryInterface $repository,
        private readonly ThirdPartyQueryRepositoryInterface $queryRepository,
        private readonly ThirdPartyFieldRepositoryInterface $fieldRepository,
        private readonly ThirdPartyAvailabilityRepositoryInterface $availabilityRepository,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function all(string $tenantId): array
    {
        $thirdParties = $this->queryRepository->all();
        $fields = $this->customFieldsFor($tenantId, $thirdParties);

        return array_map(fn (ThirdParty $t): array => $this->withCustomFields($t->toArray(), $fields[$t->id] ?? []), $thirdParties);
    }

    /** @return array<int, array<string, mixed>> */
    public function customers(string $tenantId, ?string $role = null): array
    {
        // The directory remains customer-only by default. Callers can request
        // carriers explicitly without exposing supplier or membership records.
        $allowedRoles = ['customer', 'carrier'];
        $dbRole = in_array($role, $allowedRoles, true) ? $role : 'customer';
        $thirdParties = $this->queryRepository->findByRole($dbRole);
        $fields = $this->customFieldsFor($tenantId, $thirdParties);

        return array_map(function (ThirdParty $t) use ($fields, $dbRole): array {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'identification_number' => $t->identification,
                'identification_type' => $this->toSriCode($t->identificationType, $t->identification),
                'email' => $t->email,
                'phone' => $t->phone,
                'address' => $t->address,
                'must_invoice' => $t->mustInvoice,
                'role' => $dbRole,
                'roles' => $t->roles ?? ['customer'],
                'custom_fields' => $fields[$t->id] ?? [],
            ];
        }, $thirdParties);
    }

    /** @return array<int, string> */
    public function roles(): array
    {
        return ['carrier', 'customer'];
    }

    /** @return array<int, array<string, mixed>> */
    public function carriers(string $tenantId): array
    {
        $thirdParties = $this->queryRepository->findCarriers();
        $fields = $this->customFieldsFor($tenantId, $thirdParties);

        return array_map(fn (ThirdParty $t): array => [
            'id' => $t->id,
            'name' => $t->name,
            'identification_number' => $t->identification,
            'identification_type' => $this->toSriCode($t->identificationType, $t->identification),
            'email' => $t->email,
            'phone' => $t->phone,
            'address' => $t->address,
            'must_invoice' => $t->mustInvoice,
            'role' => 'carrier',
            'roles' => $t->roles ?? ['carrier'],
            'custom_fields' => $fields[$t->id] ?? [],
        ], $thirdParties);
    }

    /** @return array<string, mixed>|null */
    public function find(string $tenantId, string $id): ?array
    {
        $thirdParty = $this->queryRepository->find($id);

        return $thirdParty === null
            ? null
            : $this->withCustomFields($thirdParty->toArray(), $this->customFieldsFor($tenantId, [$thirdParty])[$thirdParty->id] ?? []);
    }

    /** @return array<string, mixed>|null */
    public function findCustomer(string $tenantId, string $id): ?array
    {
        $thirdParty = $this->queryRepository->find($id);
        if ($thirdParty === null || ! in_array('customer', $thirdParty->roles ?? [], true)) {
            return null;
        }

        $fields = $this->customFieldsFor($tenantId, [$thirdParty]);

        return [
            'id' => $thirdParty->id,
            'name' => $thirdParty->name,
            'identification_number' => $thirdParty->identification,
            'identification_type' => $this->toSriCode($thirdParty->identificationType, $thirdParty->identification),
            'email' => $thirdParty->email,
            'phone' => $thirdParty->phone,
            'address' => $thirdParty->address,
            'must_invoice' => $thirdParty->mustInvoice,
            'role' => $thirdParty->roles[0] ?? 'customer',
            'roles' => $thirdParty->roles ?? ['customer'],
            'custom_fields' => $fields[$thirdParty->id] ?? [],
        ];
    }

    /** @return array<string, mixed> */
    public function create(ThirdPartyCreateDTO $dto): array
    {
        return (new CreateThirdPartyUseCase($this->repository))->create($dto);
    }

    /** @return array<string, mixed>|null */
    public function update(string $tenantId, string $id, ThirdPartyUpdateDTO $dto): ?array
    {
        $existing = $this->queryRepository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $dto->toArray());
        $identity = $dto->identification === null
            ? CanonicalIdentification::from($existing->identificationType, $existing->identification)
            : CanonicalIdentification::from($dto->identificationType ?? $existing->identificationType, $dto->identification);

        if ($identity !== null
            && ($identity->value !== CanonicalIdentification::normalize($existing->identification)
                || $identity->type !== CanonicalIdentification::normalizeType($existing->identificationType, $existing->identification))
            && $this->repository->identificationExists($identity->value, $identity->type)) {
            throw new DomainException('Ya existe un cliente o tercero con ese número de identificación.');
        }

        if ($identity !== null) {
            $merged['identification'] = $identity->value;
            $merged['identification_type'] = $identity->type;
        }

        $currentRoles = is_array($existing->roles) ? $existing->roles : ['customer'];
        if ($dto->roles !== null) {
            $roles = array_values(array_unique(array_merge($currentRoles, $dto->roles)));
        } elseif ($dto->role !== null) {
            $roles = array_values(array_unique(array_merge($currentRoles, [$dto->role])));
        } else {
            $roles = $currentRoles;
        }
        $merged['roles'] = $roles;

        $thirdParty = ThirdParty::fromArray($merged);

        $updated = $this->queryRepository->update($id, $thirdParty);
        if ($updated === null) {
            return null;
        }

        $customFields = $thirdParty->customFields !== null
            ? $this->fieldRepository->replaceValues($tenantId, $updated->id ?? '', $thirdParty->customFields)
            : ($this->customFieldsFor($tenantId, [$updated])[$updated->id] ?? []);

        return $this->withCustomFields($updated->toArray(), $customFields);
    }

    /** @return array<string, mixed> */
    public function identificationAvailability(string $identification, ?string $identificationType = null, ?string $excludeId = null): array
    {
        return (new ThirdPartyAvailabilityUseCase($this->availabilityRepository))->execute($identification, $identificationType, $excludeId);
    }

    /** @return array<string, mixed> */
    public function fields(string $tenantId, string $thirdPartyId): array
    {
        return $this->fieldRepository->values($tenantId, $thirdPartyId);
    }

    /** @param array<string, mixed> $values @return array<string, mixed> */
    public function updateFields(string $tenantId, string $thirdPartyId, array $values, ?string $actorId = null): array
    {
        return $this->fieldRepository->replaceValues($tenantId, $thirdPartyId, $values, $actorId);
    }

    private function toSriCode(?string $type, ?string $number): string
    {
        if ($type !== null && $type !== '') {
            return match (strtoupper($type)) {
                'RUC' => '04',
                'CED' => '05',
                'PAS' => '06',
                'CF' => '07',
                default => ctype_digit($type) ? $type : '05',
            };
        }

        $len = strlen((string) $number);

        return $len === 13 ? '04' : '05';
    }

    /** @param list<ThirdParty> $thirdParties @return array<string, array<string, mixed>> */
    private function customFieldsFor(string $tenantId, array $thirdParties): array
    {
        $ids = array_values(array_filter(array_map(static fn (ThirdParty $thirdParty): ?string => $thirdParty->id, $thirdParties)));
        if ($ids === []) {
            return [];
        }

        return $this->fieldRepository->valuesByThirdPartyIds($tenantId, $ids);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function withCustomFields(array $data, array $customFields): array
    {
        $data['custom_fields'] = $customFields;

        return $data;
    }
}
