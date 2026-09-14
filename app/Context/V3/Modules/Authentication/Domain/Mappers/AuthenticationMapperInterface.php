<?php

namespace App\Context\V3\Modules\Authentication\Domain\Mappers;

use App\Context\V3\Modules\Authentication\Domain\Models\AccessibleEnterprise;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticatedUser;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;

interface AuthenticationMapperInterface
{
    /** @param array<string, mixed> $data */
    public function user(array $data): AuthenticatedUser;

    /** @param array<string, mixed> $data */
    public function enterprise(array $data): AccessibleEnterprise;

    /** @param array<string, mixed> $data */
    public function session(array $data): AuthenticationSession;
}
