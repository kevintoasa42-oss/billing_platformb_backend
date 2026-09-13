<?php

namespace App\Context\V1\Enterprise\Application\DTOs;

class UserDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $password = null,
        /** @var RoleDTO[] */
        public array $roles = [],
        /** @var EnterpriseDTO[] */
        public array $enterprises = [],
    ) {}

    /**
     * Crea un DTO desde un array de datos de entrada (request).
     *
     * @param  array  $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
        );
    }

    /**
     * Convierte el DTO a array (sin password).
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => array_map(fn ($r) => $r->toArray(), $this->roles),
            'enterprises' => array_map(fn ($e) => $e->toArray(), $this->enterprises),
        ];
    }
}
