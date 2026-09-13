<?php

namespace App\Context\V1\Signature\Domain\Repositories;

use App\Context\V1\Signature\Domain\Models\Signature;

interface SignatureRepositoryInterface
{
    /**
     * Paginated list of signatures.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array{data: Signature[], total: int, page: int, perPage: int, lastPage: int}
     */
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array;

    public function getById(int $id): ?array;

    public function create(Signature $signature): array;

    public function update(Signature $signature): array;

    public function changeStatus(int $id, bool $status): bool;
}
