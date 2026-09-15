<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyAvailabilityRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;

final readonly class ThirdPartyAvailabilityUseCase
{
    public function __construct(
        private ThirdPartyAvailabilityRepositoryInterface $repository,
    ) {}

    /**
     * @return array{available: bool, exists: bool, identification: string, identification_type: string|null, third_party_id?: string|null, name?: string|null, roles?: list<string>}
     */
    public function execute(string $identification, ?string $identificationType = null, ?string $excludeId = null): array
    {
        $identity = CanonicalIdentification::from($identificationType, $identification);

        if ($identity === null) {
            return [
                'available' => false,
                'exists' => false,
                'identification' => '',
                'identification_type' => null,
            ];
        }

        $existing = $this->repository->findByIdentification($identity->value, $identity->type);
        $exists = $existing !== null && ($excludeId === null || $existing->id !== $excludeId);

        return [
            'available' => ! $exists,
            'exists' => $exists,
            'identification' => $identity->value,
            'identification_type' => $identity->type,
            'third_party_id' => $exists ? $existing->id : null,
            'name' => $exists ? $existing->name : null,
            'roles' => $exists ? $existing->roles : [],
        ];
    }
}
