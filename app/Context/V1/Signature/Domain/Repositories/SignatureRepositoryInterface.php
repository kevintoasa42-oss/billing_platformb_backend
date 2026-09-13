<?php

namespace App\Context\V1\Signature\Domain\Repositories;

use App\Context\V1\Signature\Domain\Models\Signature;

interface SignatureRepositoryInterface
{
    public function getById(int $id): ?array;

    public function create(Signature $signature): array;

    public function update(Signature $signature): array;

    public function changeStatus(int $id, bool $status): bool;
}
