<?php

namespace App\Context\V3\Modules\Core\SriIva\Domain\Repository;

use App\Context\V3\Modules\Core\SriIva\Domain\Models\SriIvaType;

interface SriIvaTypeRepositoryInterface
{
    /**
     * @return SriIvaType[]
     */
    public function all(): array;

    public function find(int $id): ?SriIvaType;

    public function create(array $data): SriIvaType;

    public function update(int $id, array $data): ?SriIvaType;

    public function delete(int $id): bool;
}
