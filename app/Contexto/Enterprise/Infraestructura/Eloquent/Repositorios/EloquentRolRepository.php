<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Repositorios;

use App\Contexto\Enterprise\Dominio\Mappers\RolMapperInterface;
use App\Contexto\Enterprise\Dominio\Modelos\Rol;
use App\Contexto\Enterprise\Dominio\Repositorios\RolRepositoryInterface;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\RolModel;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;

class EloquentRolRepository implements RolRepositoryInterface
{
    public function __construct(
        private RolMapperInterface $mapper,
    ) {}

    public function crear(Rol $rol): Rol
    {
        $model = RolModel::create($this->mapper->toEloquent($rol));

        return $this->mapper->toDomain($model->toArray());
    }

    public function listar(): array
    {
        return RolModel::orderBy('nombre')
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }

    public function buscarPorId(int $id): ?Rol
    {
        $model = RolModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function buscarPorNombre(string $nombre): ?Rol
    {
        $model = RolModel::where('nombre', $nombre)->first();

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function listarPorUsuario(int $usuarioId): array
    {
        $usuario = UsuarioModel::find($usuarioId);

        if (!$usuario) {
            return [];
        }

        return $usuario->roles()
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }
}
