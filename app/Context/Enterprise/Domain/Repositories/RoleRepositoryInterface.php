<?php

namespace App\Context\Enterprise\Domain\Repositories;

use App\Context\Enterprise\Domain\Models\Role;

interface RoleRepositoryInterface
{
    /**
     * Crea un rol y devuelve el modelo de dominio con el id asignado.
     *
     * @param  Role  $rol
     * @return Role
     */
    public function crear(Role $rol): Role;

    /**
     * Lista todos los roles.
     *
     * @return Role[]
     */
    public function listar(): array;

    /**
     * Busca un rol por su id.
     *
     * @param  int  $id
     * @return Role|null
     */
    public function buscarPorId(int $id): ?Role;

    /**
     * Busca un rol por su nombre.
     *
     * @param  string  $nombre
     * @return Role|null
     */
    public function buscarPorNombre(string $nombre): ?Role;

    /**
     * Lista los roles asignados a un user.
     *
     * @param  int  $userId
     * @return Role[]
     */
    public function listarPorUser(int $userId): array;
}
