<?php

namespace App\Context\Product\Application\DTOs;

class ProductDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $codigo_barras = null,
        public ?string $codigo_auxiliar = null,
        public ?string $nombre = null,
        public ?string $descripcion = null,
        public bool $estado = true,
        public float $precio_base = 0,
        /** @var int[] */
        public array $impuestos = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            codigo_barras: $data['codigo_barras'] ?? null,
            codigo_auxiliar: $data['codigo_auxiliar'] ?? null,
            nombre: $data['nombre'] ?? null,
            descripcion: $data['descripcion'] ?? null,
            estado: $data['estado'] ?? true,
            precio_base: $data['precio_base'] ?? 0,
            impuestos: $data['impuestos'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'codigo_barras' => $this->codigo_barras,
            'codigo_auxiliar' => $this->codigo_auxiliar,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'precio_base' => $this->precio_base,
            'impuestos' => $this->impuestos,
        ];
    }
}
