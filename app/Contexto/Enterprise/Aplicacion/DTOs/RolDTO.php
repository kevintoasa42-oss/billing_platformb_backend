<?php

namespace App\Contexto\Enterprise\Aplicacion\DTOs;

class RolDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $descripcion = null,
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
            descripcion: $data['descripcion'] ?? null,
        );
    }

    /**
     * Convierte el DTO a array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
        ];
    }
}
