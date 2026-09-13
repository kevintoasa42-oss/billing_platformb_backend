<?php

namespace App\Context\V1\Modules\Enterprise\Domain\Repositories;

use App\Context\V1\Modules\Enterprise\Domain\Models\User;

interface UserRepositoryInterface
{
    /**
     * Crea un user y devuelve el modelo de dominio con el id asignado.
     *
     * @param  User  $user
     * @return User
     */
    public function crear(User $user): User;

    /**
     * Busca un user por su email.
     *
     * @param  string  $email
     * @return User|null
     */
    public function buscarPorEmail(string $email): ?User;

    /**
     * Busca un user por su id, incluyendo sus roles y enterprises.
     *
     * @param  int  $id
     * @return User|null
     */
    public function buscarPorId(int $id): ?User;

    /**
     * Asigna un rol a un user.
     *
     * @param  int  $userId
     * @param  int  $rolId
     * @return void
     */
    public function asignarRol(int $userId, int $rolId): void;

    /**
     * Asigna una enterprise a un user.
     *
     * @param  int  $userId
     * @param  int  $enterpriseId
     * @return void
     */
    public function asignarEnterprise(int $userId, int $enterpriseId): void;
}
