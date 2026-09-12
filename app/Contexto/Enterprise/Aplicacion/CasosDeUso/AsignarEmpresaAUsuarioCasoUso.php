<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Dominio\Repositorios\UsuarioRepositoryInterface;

class AsignarEmpresaAUsuarioCasoUso
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    /**
     * Asigna una empresa a un usuario (un usuario puede estar en varias empresas).
     *
     * @param  int  $usuarioId
     * @param  int  $enterpriseId
     * @return void
     */
    public function ejecutar(int $usuarioId, int $enterpriseId): void
    {
        $this->usuarioRepository->asignarEmpresa($usuarioId, $enterpriseId);
    }
}
