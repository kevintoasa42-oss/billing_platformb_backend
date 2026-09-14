<?php

namespace App\Context\V3\Modules\Core\Company\Domain\Repository;

use App\Context\V3\Modules\Core\Company\Domain\Models\Company;

interface CompanyRepositoryInterface
{
    /**
     * @return array<int, Company>
     */
    public function all(): array;

    public function find(string $id): ?Company;

    public function create(Company $company): Company;

    public function update(string $id, Company $company): ?Company;
}
