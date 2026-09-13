<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Mappers;

use App\Context\Enterprise\Domain\Mappers\EnterpriseMapperInterface;
use App\Context\Enterprise\Domain\Models\Enterprise;

class EnterpriseMapper implements EnterpriseMapperInterface
{
    public function toDomain(array $data): Enterprise
    {
        return new Enterprise(
            id: $data['id'] ?? null,
            nombre: $data['nombre'] ?? null,
            ruc: $data['ruc'] ?? null,
            tradename: $data['tradename'] ?? null,
            matrixname: $data['matrixname'] ?? null,
            telefono: $data['telefono'] ?? null,
            correo_corporativo: $data['correo_corporativo'] ?? null,
            db_name: $data['db_name'] ?? null,
        );
    }

    public function toEloquent(Enterprise $enterprise): array
    {
        return [
            'nombre' => $enterprise->nombre,
            'ruc' => $enterprise->ruc,
            'tradename' => $enterprise->tradename,
            'matrixname' => $enterprise->matrixname,
            'telefono' => $enterprise->telefono,
            'correo_corporativo' => $enterprise->correo_corporativo,
            'db_name' => $enterprise->getDbName(),
        ];
    }
}
