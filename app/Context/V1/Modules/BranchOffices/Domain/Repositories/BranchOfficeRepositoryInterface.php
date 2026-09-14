<?php

namespace App\Context\V1\Modules\BranchOffices\Domain\Repositories;

use App\Context\V1\Modules\BranchOffices\Domain\Models\BranchOffice;

interface BranchOfficeRepositoryInterface
{
    /** @return array{data: BranchOffice[], total: int, page: int, perPage: int, lastPage: int} */
    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array;

    public function findById(int $id): ?BranchOffice;

    public function create(BranchOffice $branchOffice): BranchOffice;

    public function update(BranchOffice $branchOffice): BranchOffice;

    public function delete(int $id): bool;
}
