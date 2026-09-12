<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Dominio\Repositorios\UsuarioRepositoryInterface;

class AsignarRolAUsuarioCasoUso
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    /**
     * Asigna un rol a un usuario.
     *
     * @param  int  $usuarioId
     * @param  int  $rolId
     * @return void
     */
    public function ejecutar(int $usuarioId, int $rolId): void
    {
        $this->usuarioRepository->asignarRol($usuarioId, $rolId);
    }
}
