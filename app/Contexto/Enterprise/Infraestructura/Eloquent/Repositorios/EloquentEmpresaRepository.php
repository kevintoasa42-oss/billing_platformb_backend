<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Repositorios;

use App\Contexto\Enterprise\Dominio\Mappers\EmpresaMapperInterface;
use App\Contexto\Enterprise\Dominio\Modelos\Empresa;
use App\Contexto\Enterprise\Dominio\Repositorios\EmpresaRepositoryInterface;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\EmpresaModel;

class EloquentEmpresaRepository implements EmpresaRepositoryInterface
{
    public function __construct(
        private EmpresaMapperInterface $mapper,
    ) {}

    public function crear(Empresa $empresa): Empresa
    {
        $model = EmpresaModel::create($this->mapper->toEloquent($empresa));

        return $this->mapper->toDomain($model->toArray());
    }

    public function listar(): array
    {
        return EmpresaModel::orderBy('nombre')
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }

    public function buscarPorId(int $id): ?Empresa
    {
        $model = EmpresaModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function buscarPorRuc(string $ruc): ?Empresa
    {
        $model = EmpresaModel::where('ruc', $ruc)->first();

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function listarPorUsuario(int $usuarioId): array
    {
        $usuario = \App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel::find($usuarioId);

        if (!$usuario) {
            return [];
        }

        return $usuario->empresas()
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }
}
