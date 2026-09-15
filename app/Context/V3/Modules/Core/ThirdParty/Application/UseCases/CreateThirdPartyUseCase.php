<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Exceptions\ThirdPartyException;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;

/**
 * Creates a canonical ThirdParty with the requested roles.
 */
final class CreateThirdPartyUseCase
{
    /** @var array<int, string> */
    private const ALLOWED_ROLES = ['customer', 'carrier', 'supplier', 'member'];

    public function __construct(
        private readonly ThirdPartyRepositoryInterface $repository,
    ) {}

    /** @return array<string, mixed> */
    public function create(ThirdPartyCreateDTO $dto): array
    {
        $roles = $this->normalizeRoles($dto->roles, $dto->role);

        $identity = CanonicalIdentification::from($dto->identificationType, $dto->identification);
        if ($identity === null) {
            throw new ThirdPartyException('La identificación fiscal es obligatoria.', 422, 'third_party_fields_invalid');
        }

        if ($this->repository->identificationExists($identity->value, $identity->type)) {
            throw new ThirdPartyException(
                'Ya existe un cliente o tercero con ese número de identificación.',
                409,
                'third_party_identification_exists',
            );
        }

        $thirdParty = ThirdParty::fromArray([
            ...$dto->toArray(),
            'identification' => $identity->value,
            'identification_type' => $identity->type,
            'roles' => $roles,
            'must_invoice' => $dto->mustInvoice ?? true,
            'is_active' => $dto->isActive ?? true,
        ]);

        $created = $this->repository->create($thirdParty);

        return [
            ...$created->toArray(),
            'custom_fields' => $thirdParty->customFields ?? [],
        ];
    }

    /**
     * @param array<int, string> $roles
     * @return array<int, string>
     */
    private function normalizeRoles(array $roles, ?string $legacyRole = null): array
    {
        $roles = $roles === [] && $legacyRole !== null ? [$legacyRole] : $roles;
        $roles = array_values(array_unique(array_map(static fn (string $role): string => strtolower(trim($role)), $roles)));
        $invalid = array_values(array_diff($roles, self::ALLOWED_ROLES));
        if ($invalid !== []) {
            throw new ThirdPartyException('El rol de tercero no es válido.', 422, 'third_party_role_invalid');
        }

        return $roles === [] ? ['customer'] : $roles;
    }
}
