<?php

namespace App\Contexto\Enterprise\Dominio\Modelos;

class Empresa
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $ruc = null,
        public ?string $tradename = null,
        public ?string $matrixname = null,
        public ?string $telefono = null,
        public ?string $correo_corporativo = null,
        public ?string $db_name = null,
    ) {}

    public function getDbName(): ?string
    {
        return $this->db_name ?? $this->ruc;
    }
}
