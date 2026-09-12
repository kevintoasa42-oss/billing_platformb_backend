<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Mappers;

use App\Contexto\Enterprise\Dominio\Mappers\EmpresaMapperInterface;
use App\Contexto\Enterprise\Dominio\Modelos\Empresa;

class EmpresaMapper implements EmpresaMapperInterface
{
    public function toDomain(array $data): Empresa
    {
        return new Empresa(
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

    public function toEloquent(Empresa $empresa): array
    {
        return [
            'nombre' => $empresa->nombre,
            'ruc' => $empresa->ruc,
            'tradename' => $empresa->tradename,
            'matrixname' => $empresa->matrixname,
            'telefono' => $empresa->telefono,
            'correo_corporativo' => $empresa->correo_corporativo,
            'db_name' => $empresa->getDbName(),
        ];
    }
}
