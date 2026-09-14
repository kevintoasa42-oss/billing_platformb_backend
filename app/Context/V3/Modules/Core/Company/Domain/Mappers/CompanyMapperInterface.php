<?php

namespace App\Context\V3\Modules\Core\Company\Domain\Mappers;

use App\Context\V3\Modules\Core\Company\Domain\Models\Company;
use App\Context\V3\Modules\Core\Company\Infrastructure\Laravel\Eloquent\Models\CompanyModel;

interface CompanyMapperInterface
{
    public function toDomain(CompanyModel $record): Company;

    /**
     * @param  iterable<CompanyModel>  $records
     * @return array<int, Company>
     */
    public function toDomainList(iterable $records): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseArray(Company $company): array;
}
