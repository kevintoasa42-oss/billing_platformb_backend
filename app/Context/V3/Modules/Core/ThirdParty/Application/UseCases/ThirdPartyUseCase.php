<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierAffiliationRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierCompanyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ThirdPartyUseCase
{
    public function __construct(
        private readonly ThirdPartyRepositoryInterface $repository,
        private readonly CarrierCompanyRepositoryInterface $carrierCompanyRepository,
        private readonly CarrierAffiliationRepositoryInterface $affiliationRepository,
        private readonly ?ThirdPartyFieldRepositoryInterface $fieldRepository = null,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $thirdParties = $this->repository->all();

        $fields = $this->customFieldsFor($thirdParties);

        return array_map(fn (ThirdParty $t): array => $this->withCustomFields($t->toArray(), $fields[$t->id] ?? []), $thirdParties);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function customers(?string $role = null): array
    {
        // The directory remains customer-only by default. Callers can request
        // carriers explicitly without exposing supplier or membership records.
        $allowedRoles = ['customer', 'carrier'];
        $dbRole = in_array($role, $allowedRoles, true) ? $role : 'customer';
        $thirdParties = $this->repository->findByRole($dbRole);

        $thirdPartyIds = array_map(fn (ThirdParty $t) => $t->id, $thirdParties);
        $platesMap = $this->affiliationRepository->platesByThirdPartyIds($thirdPartyIds);
        $fields = $this->customFieldsFor($thirdParties);

        return array_map(function (ThirdParty $t) use ($platesMap, $fields, $dbRole): array {
            $plates = $platesMap[$t->id] ?? [];

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
                'plate' => $plates[0] ?? null,
                'custom_fields' => $fields[$t->id] ?? [],
            ];
        }, $thirdParties);
    }

    /**
     * @return array<int, string>
     */
    public function roles(): array
    {
        return ['carrier', 'customer'];
    }

    /**
     * Socios transportistas: rol carrier. `must_invoice` no participa en la
     * selección porque es un campo histórico de facturación.
     *
     * @return array<int, array<string, mixed>>
     */
    public function carriers(): array
    {
        $thirdParties = $this->repository->findCarriers();

        $thirdPartyIds = array_map(fn (ThirdParty $t) => $t->id, $thirdParties);
        $platesMap = $this->affiliationRepository->platesByThirdPartyIds($thirdPartyIds);

        return array_map(function (ThirdParty $t) use ($platesMap): array {
            $plates = $platesMap[$t->id] ?? [];
            $carrierCompany = $this->carrierCompanyRepository->findByThirdPartyId($t->id);

            return [
                'id' => $t->id,
                'carrier_company_id' => $carrierCompany?->id,
                'name' => $t->name,
                'identification_number' => $t->identification,
                'identification_type' => $this->toSriCode($t->identificationType, $t->identification),
                'email' => $t->email,
                'phone' => $t->phone,
                'address' => $t->address,
                'must_invoice' => $t->mustInvoice,
                'role' => 'carrier',
                'roles' => $t->roles ?? ['carrier'],
                'plate' => $plates[0] ?? null,
            ];
        }, $thirdParties);
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

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $thirdParty = $this->repository->find($id);

        return $thirdParty === null ? null : $this->withCustomFields($thirdParty->toArray(), $this->customFieldsFor([$thirdParty])[$thirdParty->id] ?? []);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findCustomer(string $id): ?array
    {
        $thirdParty = $this->repository->find($id);
        if ($thirdParty === null || ! in_array('customer', $thirdParty->roles ?? [], true)) {
            return null;
        }

        $platesMap = $this->affiliationRepository->platesByThirdPartyIds([$thirdParty->id]);
        $plates = $platesMap[$thirdParty->id] ?? [];
        $fields = $this->customFieldsFor([$thirdParty]);

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
            'plate' => $plates[0] ?? null,
            'custom_fields' => $fields[$thirdParty->id] ?? [],
        ];
    }

    /** @return array<string,mixed> */
    public function identificationAvailability(string $identification, ?string $identificationType = null, ?string $excludeId = null): array
    {
        $identity = CanonicalIdentification::from($identificationType, $identification);
        if ($identity === null) {
            return ['available' => false, 'exists' => false, 'identification' => '', 'identification_type' => null];
        }
        $existing = $this->repository->findByIdentification($identity->value, $identity->type);
        $same = $existing !== null && ($excludeId === null || $existing->id !== $excludeId);

        return [
            'available' => ! $same,
            'exists' => $same,
            'identification' => $identity->value,
            'identification_type' => $identity->type,
            'third_party_id' => $same ? $existing->id : null,
            'name' => $same ? $existing->name : null,
            'roles' => $same ? ($existing->roles ?? []) : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function create(ThirdPartyCreateDTO $dto): array
    {
        $data = $dto->toArray();

        $roles = $this->normalizeRoles($dto->roles, $dto->role);
        $data['roles'] = $roles;

        $identity = CanonicalIdentification::from($dto->identificationType, $dto->identification);
        if ($identity !== null) {
            $data['identification'] = $identity->value;
            $data['identification_type'] = $identity->type;
        }

        $thirdParty = ThirdParty::fromArray($data);

        if ($identity !== null && $this->repository->identificationExists($identity->value, null, $identity->type)) {
            throw new ConflictHttpException('Ya existe un cliente o tercero con ese número de identificación.');
        }

        return DB::connection('master_v3')->transaction(function () use ($thirdParty): array {
            $created = $this->repository->create($thirdParty);
            $customFields = $this->fieldRepository !== null && array_intersect($thirdParty->roles ?? [], ['customer', 'carrier']) !== []
                ? $this->fieldRepository->replaceValues($created->tenantId ?? '', $created->id ?? '', $thirdParty->customFields ?? [])
                : [];

            return $this->withCustomFields($created->toArray(), $customFields);
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function update(string $id, ThirdPartyUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

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
            && $this->repository->identificationExists($identity->value, $id, $identity->type)) {
            throw new ConflictHttpException('Ya existe un cliente o tercero con ese número de identificación.');
        }

        if ($identity !== null) {
            $merged['identification'] = $identity->value;
            $merged['identification_type'] = $identity->type;
        }

        $currentRoles = is_array($existing->roles) ? $existing->roles : ['customer'];
        if ($dto->roles !== null) {
            $roles = array_values(array_unique(array_merge($currentRoles, $this->normalizeRoles($dto->roles, null))));
        } elseif ($dto->role !== null) {
            $roles = array_values(array_unique(array_merge($currentRoles, $this->normalizeRoles([], $dto->role))));
        } else {
            $roles = $currentRoles;
        }
        $merged['roles'] = $roles;

        $thirdParty = ThirdParty::fromArray($merged);

        return DB::connection('master_v3')->transaction(function () use ($id, $thirdParty): ?array {
            $updated = $this->repository->update($id, $thirdParty);
            if ($updated === null) {
                return null;
            }

            $customFields = $thirdParty->customFields !== null
                ? ($this->fieldRepository !== null && array_intersect($thirdParty->roles ?? [], ['customer', 'carrier']) !== []
                    ? $this->fieldRepository->replaceValues($updated->tenantId ?? '', $updated->id ?? '', $thirdParty->customFields)
                    : [])
                : ($this->customFieldsFor([$updated])[$updated->id] ?? []);

            return $this->withCustomFields($updated->toArray(), $customFields);
        });
    }

    /** @param  list<ThirdParty>  $thirdParties  @return array<string, array<string, mixed>> */
    private function customFieldsFor(array $thirdParties): array
    {
        if ($this->fieldRepository === null) {
            return [];
        }

        $ids = array_values(array_filter(array_map(static fn (ThirdParty $thirdParty): ?string => $thirdParty->id, $thirdParties)));
        if ($ids === []) {
            return [];
        }

        return $this->fieldRepository->valuesByThirdPartyIds((string) ($thirdParties[0]->tenantId ?? ''), $ids);
    }

    /** @param  array<string, mixed>  $data  @return array<string, mixed> */
    private function withCustomFields(array $data, array $customFields): array
    {
        $data['custom_fields'] = $customFields;

        return $data;
    }

    /** @param  array<int, string>  $roles  @return array<int, string> */
    private function normalizeRoles(array $roles, ?string $legacyRole): array
    {
        $roles = $roles === [] && $legacyRole !== null ? [$legacyRole] : $roles;
        $roles = array_values(array_unique(array_map(static fn (string $role): string => strtolower(trim($role)), $roles)));
        $allowed = ['customer', 'carrier', 'supplier', 'member'];
        $invalid = array_values(array_diff($roles, $allowed));
        if ($invalid !== []) {
            throw new ConflictHttpException('El rol de tercero no es válido.');
        }

        return $roles === [] ? ['customer'] : $roles;
    }
}
