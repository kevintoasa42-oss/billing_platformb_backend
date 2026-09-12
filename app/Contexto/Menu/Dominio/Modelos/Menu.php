<?php

namespace App\Contexto\Menu\Dominio\Modelos;

class Menu
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $ruta = null,
        public ?string $icono = null,
        public ?int $parent_id = null,
        public int $orden = 0,
        /** @var Menu[] */
        public array $hijos = [],
    ) {}
}
