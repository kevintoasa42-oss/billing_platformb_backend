<?php

namespace App\Context\V1\Modules\BranchOffices\Domain\Mappers;

use App\Context\V1\Modules\BranchOffices\Domain\Models\BranchOffice;

interface BranchOfficeMapperInterface
{
    public function toDomain(array $data): BranchOffice;

    /** Data accepted by the persistence implementation. */
    public function toPersistence(BranchOffice $branchOffice): array;

    /** Complete serializable representation used when applying partial changes. */
    public function toArray(BranchOffice $branchOffice): array;
}
