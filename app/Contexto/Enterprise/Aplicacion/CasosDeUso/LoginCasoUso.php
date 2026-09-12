<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Aplicacion\DTOs\LoginDTO;
use App\Contexto\Enterprise\Dominio\Repositorios\EmpresaRepositoryInterface;
use App\Contexto\Enterprise\Dominio\Repositorios\UsuarioRepositoryInterface;
use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;

class LoginCasoUso
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarioRepository,
        private EmpresaRepositoryInterface $empresaRepository,
    ) {}

    /**
     * Valida credenciales, selecciona la empresa y emite un token Sanctum
     * con el enterprise_id correspondiente.
     *
     * @param  LoginDTO  $dto
     * @return array{token: string, usuario: array, empresa: array}
     * @throws \Exception Si las credenciales son inválidas o la empresa no pertenece al usuario.
     */
    public function ejecutar(LoginDTO $dto): array
    {
        // Buscar usuario por email.
        $usuarioModel = UsuarioModel::where('email', $dto->email)->first();

        if (!$usuarioModel) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        // Validar password.
        if (!password_verify($dto->password, $usuarioModel->password)) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        // Validar que la empresa pertenece al usuario.
        $empresa = $usuarioModel->empresas()->where('enterprise_id', $dto->enterprise_id)->first();

        if (!$empresa) {
            throw new \Exception('La empresa seleccionada no pertenece al usuario.', 403);
        }

        // Emitir token Sanctum con el enterprise_id.
        $token = $usuarioModel->createToken(
            name: 'auth_token',
            abilities: ['*'],
        );

        // Asignar enterprise_id al token.
        $token->accessToken->forceFill([
            'enterprise_id' => $dto->enterprise_id,
        ])->save();

        return [
            'token' => $token->plainTextToken,
            'usuario' => [
                'id' => $usuarioModel->id,
                'nombre' => $usuarioModel->nombre,
                'email' => $usuarioModel->email,
            ],
            'empresa' => [
                'id' => $empresa->id,
                'nombre' => $empresa->nombre,
                'ruc' => $empresa->ruc,
            ],
        ];
    }
}
