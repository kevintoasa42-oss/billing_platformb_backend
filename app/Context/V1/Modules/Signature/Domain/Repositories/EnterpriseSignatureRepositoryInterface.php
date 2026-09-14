<?php

namespace App\Context\V1\Modules\Signature\Domain\Repositories;

use App\Context\V1\Modules\Signature\Domain\Models\EnterpriseSignature;

interface EnterpriseSignatureRepositoryInterface
{
    public function getByEnterpriseId(int $enterpriseId): ?array;

    public function create(EnterpriseSignature $signature): array;

    public function update(EnterpriseSignature $signature): array;

    public function changeStatus(int $id, bool $status): bool;
}
