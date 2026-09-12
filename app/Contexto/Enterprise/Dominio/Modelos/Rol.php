<?php

namespace App\Contexto\Enterprise\Dominio\Modelos;

class Rol
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $descripcion = null,
    ) {}
}
