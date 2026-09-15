<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Exceptions\ThirdPartyException;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;

final readonly class CreateThirdPartyUseCase
{
    public function __construct(
        private ThirdPartyRepositoryInterface $repository,
    ) {}

    /** @return array<string, mixed> */
    public function create(ThirdPartyCreateDTO $dto): array
    {
        $identity = CanonicalIdentification::from($dto->identificationType, $dto->identification)
            ?? throw new ThirdPartyException('La identificación del tercero es obligatoria.', 'validation_failed');

        if ($this->repository->identificationExists($identity->value, $identity->type)) {
            throw new ThirdPartyException(
                'Ya existe un cliente o tercero con ese número de identificación.',
                'identification_already_exists',
                409,
            );
        }

        $data = $dto->toArray();
        $data['identification'] = $identity->value;
        $data['identification_type'] = $identity->type;
        $data['roles'] = $this->normalizeRoles($dto->roles, $dto->role);

        return $this->repository->create(ThirdParty::fromArray($data))->toArray();
    }

    /** @param list<string> $roles @return list<string> */
    private function normalizeRoles(array $roles, ?string $legacyRole): array
    {
        $roles = $roles === [] && $legacyRole !== null ? [$legacyRole] : $roles;
        $roles = array_values(array_unique(array_map(
            static fn (string $role): string => strtolower(trim($role)),
            $roles,
        )));

        $allowed = ['customer', 'carrier', 'supplier', 'member'];
        if (array_diff($roles, $allowed) !== []) {
            throw new ThirdPartyException('El rol de tercero no es válido.', 'third_party_role_invalid');
        }

        return $roles === [] ? ['customer'] : $roles;
    }
}
