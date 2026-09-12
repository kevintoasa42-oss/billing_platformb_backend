<?php

namespace App\Contexto\Enterprise\Aplicacion\DTOs;

class UsuarioDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $email = null,
        public ?string $password = null,
        /** @var RolDTO[] */
        public array $roles = [],
        /** @var EmpresaDTO[] */
        public array $empresas = [],
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
            nombre: $data['nombre'] ?? null,
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
            'nombre' => $this->nombre,
            'email' => $this->email,
            'roles' => array_map(fn ($r) => $r->toArray(), $this->roles),
            'empresas' => array_map(fn ($e) => $e->toArray(), $this->empresas),
        ];
    }
}
