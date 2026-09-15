<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyAvailabilityRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use InvalidArgumentException;

/**
 * Checks whether a canonical identification is free for a new ThirdParty.
 */
final class ThirdPartyAvailabilityUseCase
{
    public function __construct(
        private readonly ThirdPartyAvailabilityRepositoryInterface $repository,
    ) {}

    /** @return array<string, mixed> */
    public function execute(string $identification, ?string $identificationType = null, ?string $excludeId = null): array
    {
        try {
            $identity = CanonicalIdentification::from($identificationType, $identification);
        } catch (InvalidArgumentException $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        }

        if ($identity === null) {
            return [
                'available' => false,
                'exists' => false,
                'identification' => '',
                'identification_type' => null,
            ];
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
            'roles' => $same ? $existing->roles : [],
        ];
    }
}
