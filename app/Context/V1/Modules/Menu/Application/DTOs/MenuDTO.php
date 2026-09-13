<?php

namespace App\Context\V1\Modules\Menu\Application\DTOs;

class MenuDTO implements \JsonSerializable
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $route = null,
        public ?string $icon = null,
        public ?int $parent_id = null,
        public int $order = 0,
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
            name: $data['name'] ?? null,
            route: $data['route'] ?? null,
            icon: $data['icon'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            order: $data['order'] ?? 0,
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
            'label' => $this->name,
            'href' => $this->route,
            'icon' => $this->icon,
            'order' => $this->order,
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
