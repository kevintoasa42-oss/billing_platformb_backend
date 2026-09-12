<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Aplicacion\DTOs\UsuarioDTO;
use App\Contexto\Enterprise\Dominio\Modelos\Usuario;
use App\Contexto\Enterprise\Dominio\Repositorios\UsuarioRepositoryInterface;

class CrearUsuarioCasoUso
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    /**
     * Crea un usuario.
     *
     * @param  UsuarioDTO  $dto
     * @return UsuarioDTO
     */
    public function ejecutar(UsuarioDTO $dto): UsuarioDTO
    {
        $usuario = new Usuario(
            nombre: $dto->nombre,
            email: $dto->email,
            password: $dto->password,
        );

        $usuario = $this->usuarioRepository->crear($usuario);

        return UsuarioDTO::fromArray([
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'email' => $usuario->email,
        ]);
    }
}
