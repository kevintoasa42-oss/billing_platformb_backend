<?php

namespace App\Contexto\Enterprise\Dominio\Modelos;

class Usuario
{
    public function __construct(
        public ?int $id = null,
        public ?string $nombre = null,
        public ?string $email = null,
        public ?string $password = null,
        /** @var Rol[] */
        public array $roles = [],
        /** @var Empresa[] */
        public array $empresas = [],
    ) {}
}
