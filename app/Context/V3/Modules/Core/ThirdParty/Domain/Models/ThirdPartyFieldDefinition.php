<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Configurable custom field definition for a ThirdParty scope.
 */
final class ThirdPartyFieldDefinition
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $label,
        public readonly string $scope,
        public readonly string $dataType,
        /** @var array<string, mixed> */
        public readonly array $validation,
        public readonly int $sortOrder,
        public readonly bool $isRequired,
        public readonly bool $isActive,
    ) {}
}
