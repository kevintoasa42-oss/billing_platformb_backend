<?php

namespace App\Context\V3\Shared\Tenant\Domain\Models;

use InvalidArgumentException;

/** Immutable tenant identity propagated through a V3 request. */
final class TenantContext
{
    public function __construct(public readonly string $tenantId)
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $tenantId) !== 1) {
            throw new InvalidArgumentException('El identificador del tenant V3 no es un UUID válido.');
        }
    }
}
