<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Mappers;

use App\Context\Enterprise\Domain\Mappers\UserMapperInterface;
use App\Context\Enterprise\Domain\Models\Enterprise;
use App\Context\Enterprise\Domain\Models\Role;
use App\Context\Enterprise\Domain\Models\User;

class UserMapper implements UserMapperInterface
{
    public function __construct(
        private RoleMapper $rolMapper,
        private EnterpriseMapper $enterpriseMapper,
    ) {}

    public function toDomain(array $data): User
    {
        $roles = [];
        if (!empty($data['roles'])) {
            foreach ($data['roles'] as $rolData) {
                $roles[] = $this->rolMapper->toDomain(is_array($rolData) ? $rolData : $rolData->toArray());
            }
        }

        $enterprises = [];
        if (!empty($data['enterprises'])) {
            foreach ($data['enterprises'] as $enterpriseData) {
                $enterprises[] = $this->enterpriseMapper->toDomain(is_array($enterpriseData) ? $enterpriseData : $enterpriseData->toArray());
            }
        }

        return new User(
            id: $data['id'] ?? null,
            nombre: $data['nombre'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            roles: $roles,
            enterprises: $enterprises,
        );
    }

    public function toEloquent(User $user): array
    {
        return [
            'nombre' => $user->nombre,
            'email' => $user->email,
            'password' => $user->password,
        ];
    }
}
