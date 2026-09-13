<?php

namespace App\Contexto\Product\Dominio\Modelos;

class Producto
{
    public function __construct(
        public ?int $id = null,
        public ?string $codigo_barras = null,
        public ?string $codigo_auxiliar = null,
        public ?string $nombre = null,
        public ?string $descripcion = null,
        public bool $estado = true,
        public float $precio_base = 0,
        /** @var int[] IDs de sri_iva_percentages (DB central) */
        public array $impuestos = [],
    ) {}

    public function activar(): void
    {
        $this->estado = true;
    }

    public function desactivar(): void
    {
        $this->estado = false;
    }
}
