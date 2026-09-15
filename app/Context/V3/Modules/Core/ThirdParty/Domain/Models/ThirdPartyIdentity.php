<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Minimal third-party read model required by identity availability checks.
 *
 * @param  list<string>  $roles
 */
final readonly class ThirdPartyIdentity
{
    public function __construct(
        public string $id,
        public string $name,
        public array $roles,
    ) {}
}
