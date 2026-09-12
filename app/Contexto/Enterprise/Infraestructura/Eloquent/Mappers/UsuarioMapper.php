<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Mappers;

use App\Contexto\Enterprise\Dominio\Mappers\UsuarioMapperInterface;
use App\Contexto\Enterprise\Dominio\Modelos\Empresa;
use App\Contexto\Enterprise\Dominio\Modelos\Rol;
use App\Contexto\Enterprise\Dominio\Modelos\Usuario;

class UsuarioMapper implements UsuarioMapperInterface
{
    public function __construct(
        private RolMapper $rolMapper,
        private EmpresaMapper $empresaMapper,
    ) {}

    public function toDomain(array $data): Usuario
    {
        $roles = [];
        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $rolData) {
                $roles[] = $this->rolMapper->toDomain(is_array($rolData) ? $rolData : $rolData->toArray());
            }
        }

        $empresas = [];
        if (!empty($data['empresas'])) {
            foreach ($data['empresas'] as $empresaData) {
                $empresas[] = $this->empresaMapper->toDomain(is_array($empresaData) ? $empresaData : $empresaData->toArray());
            }
        }

        return new Usuario(
            id: $data['id'] ?? null,
            nombre: $data['nombre'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            roles: $roles,
            empresas: $empresas,
        );
    }

    public function toEloquent(Usuario $usuario): array
    {
        return [
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
            'password' => $usuario->password,
        ];
    }
}
