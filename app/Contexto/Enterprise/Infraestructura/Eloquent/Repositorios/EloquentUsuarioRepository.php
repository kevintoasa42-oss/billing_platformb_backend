<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Repositorios;

use App\Contexto\Enterprise\Dominio\Mappers\UsuarioMapperInterface;
use App\Contexto\Enterprise\Dominio\Modelos\Usuario;
use App\Contexto\Enterprise\Dominio\Repositorios\UsuarioRepositoryInterface;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;

class EloquentUsuarioRepository implements UsuarioRepositoryInterface
{
    public function __construct(
        private UsuarioMapperInterface $mapper,
    ) {}

    public function crear(Usuario $usuario): Usuario
    {
        $model = UsuarioModel::create($this->mapper->toEloquent($usuario));

        return $this->mapper->toDomain($model->toArray());
    }

    public function buscarPorEmail(string $email): ?Usuario
    {
        $model = UsuarioModel::where('email', $email)->first();

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function buscarPorId(int $id): ?Usuario
    {
        $model = UsuarioModel::with(['roles', 'empresas'])->find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function asignarRol(int $usuarioId, int $rolId): void
    {
        $model = UsuarioModel::find($usuarioId);
        if ($model) {
            $model->roles()->syncWithoutDetaching([$rolId]);
        }
    }

    public function asignarEmpresa(int $usuarioId, int $enterpriseId): void
    {
        $model = UsuarioModel::find($usuarioId);
        if ($model) {
            $model->empresas()->syncWithoutDetaching([$enterpriseId]);
        }
    }
}
