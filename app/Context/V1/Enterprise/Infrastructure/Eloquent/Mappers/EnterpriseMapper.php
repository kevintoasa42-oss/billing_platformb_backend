<?php

namespace App\Context\V1\Enterprise\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Enterprise\Domain\Mappers\EnterpriseMapperInterface;
use App\Context\V1\Enterprise\Domain\Models\Enterprise;

class EnterpriseMapper implements EnterpriseMapperInterface
{
    public function toDomain(array $data): Enterprise
    {
        return new Enterprise(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            ruc: $data['ruc'] ?? null,
            tradename: $data['tradename'] ?? null,
            matrix_name: $data['matrix_name'] ?? null,
            phone: $data['phone'] ?? null,
            corporate_email: $data['corporate_email'] ?? null,
            db_name: $data['db_name'] ?? null,
        );
    }

    public function toEloquent(Enterprise $enterprise): array
    {
        return [
            'name' => $enterprise->name,
            'ruc' => $enterprise->ruc,
            'tradename' => $enterprise->tradename,
            'matrix_name' => $enterprise->matrix_name,
            'phone' => $enterprise->phone,
            'corporate_email' => $enterprise->corporate_email,
            'db_name' => $enterprise->getDbName(),
        ];
    }
}
