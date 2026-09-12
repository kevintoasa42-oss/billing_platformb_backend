<?php

namespace App\Contexto\Enterprise\Dominio\Repositorios;

use App\Contexto\Enterprise\Dominio\Modelos\Rol;

interface RolRepositoryInterface
{
    /**
     * Crea un rol y devuelve el modelo de dominio con el id asignado.
     *
     * @param  Rol  $rol
     * @return Rol
     */
    public function crear(Rol $rol): Rol;

    /**
     * Lista todos los roles.
     *
     * @return Rol[]
     */
    public function listar(): array;

    /**
     * Busca un rol por su id.
     *
     * @param  int  $id
     * @return Rol|null
     */
    public function buscarPorId(int $id): ?Rol;

    /**
     * Busca un rol por su nombre.
     *
     * @param  string  $nombre
     * @return Rol|null
     */
    public function buscarPorNombre(string $nombre): ?Rol;

    /**
     * Lista los roles asignados a un usuario.
     *
     * @param  int  $usuarioId
     * @return Rol[]
     */
    public function listarPorUsuario(int $usuarioId): array;
}
