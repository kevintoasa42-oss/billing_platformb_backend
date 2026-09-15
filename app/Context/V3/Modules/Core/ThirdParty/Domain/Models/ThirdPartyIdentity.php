<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Lightweight identity projection used for availability checks.
 */
final class ThirdPartyIdentity
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        /** @var array<int, string> */
        public readonly array $roles,
    ) {}
}
