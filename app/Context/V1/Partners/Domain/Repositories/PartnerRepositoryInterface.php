<?php

namespace App\Context\V1\Partners\Domain\Repositories;

use App\Context\V1\Partners\Domain\Models\Partner;

interface PartnerRepositoryInterface
{
    /** @return array{data: Partner[], total: int, page: int, perPage: int, lastPage: int} */
    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array;

    public function findById(int $id): ?Partner;

    public function create(Partner $partner): Partner;

    public function update(Partner $partner): Partner;

    public function delete(int $id): bool;
}
