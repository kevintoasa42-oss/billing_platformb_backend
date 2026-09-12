<?php

namespace App\Contexto\Menu\Aplicacion\DTOs;

class MenuDTO implements \JsonSerializable
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
     * Convierte el DTO a array con el formato que el frontend necesita:
     * { label, href, icon, order, children }
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->nombre,
            'href' => $this->ruta,
            'icon' => $this->icono,
            'order' => $this->orden,
            'children' => array_map(fn ($h) => $h->toArray(), $this->hijos),
        ];
    }

    /**
     * Implementacion de JsonSerializable para que Laravel serialice
     * el DTO usando toArray() en lugar de las propiedades publicas.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
