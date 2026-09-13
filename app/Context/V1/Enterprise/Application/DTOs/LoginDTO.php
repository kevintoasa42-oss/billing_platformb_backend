<?php

namespace App\Context\V1\Enterprise\Application\DTOs;

class LoginDTO
{
    public function __construct(
        public ?string $email = null,
        public ?string $password = null,
        public ?int $enterprise_id = null,
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
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            enterprise_id: $data['enterprise_id'] ?? null,
        );
    }
}
