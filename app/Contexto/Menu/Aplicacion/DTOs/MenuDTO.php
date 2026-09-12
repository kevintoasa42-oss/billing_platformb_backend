<?php

namespace App\Contexto\Menu\Aplicacion\DTOs;

class MenuDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $ruta = null,
        public ?string $icono = null,
        public ?int $parent_id = null,
        public int $orden = 0,
        /** @var MenuDTO[] */
        public array $hijos = [],
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
            ruta: $data['ruta'] ?? null,
            icono: $data['icono'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            orden: $data['orden'] ?? 0,
        );
    }

    /**
     * Convierte el DTO a array (incluye hijos recursivamente).
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'ruta' => $this->ruta,
            'icono' => $this->icono,
            'parent_id' => $this->parent_id,
            'orden' => $this->orden,
            'hijos' => array_map(fn ($h) => $h->toArray(), $this->hijos),
        ];
    }
}
