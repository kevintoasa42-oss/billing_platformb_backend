<?php

namespace App\Contexto\Enterprise\Dominio\Repositorios;

use App\Contexto\Enterprise\Dominio\Modelos\Usuario;

interface UsuarioRepositoryInterface
{
    /**
     * Crea un usuario y devuelve el modelo de dominio con el id asignado.
     *
     * @param  Usuario  $usuario
     * @return Usuario
     */
    public function crear(Usuario $usuario): Usuario;

    /**
     * Busca un usuario por su email.
     *
     * @param  string  $email
     * @return Usuario|null
     */
    public function buscarPorEmail(string $email): ?Usuario;

    /**
     * Busca un usuario por su id, incluyendo sus roles y empresas.
     *
     * @param  int  $id
     * @return Usuario|null
     */
    public function buscarPorId(int $id): ?Usuario;

    /**
     * Asigna un rol a un usuario.
     *
     * @param  int  $usuarioId
     * @param  int  $rolId
     * @return void
     */
    public function asignarRol(int $usuarioId, int $rolId): void;

    /**
     * Asigna una empresa a un usuario.
     *
     * @param  int  $usuarioId
     * @param  int  $enterpriseId
     * @return void
     */
    public function asignarEmpresa(int $usuarioId, int $enterpriseId): void;
}
