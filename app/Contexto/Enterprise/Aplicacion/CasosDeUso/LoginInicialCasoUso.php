<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Infraestructura\Eloquent\Models\UsuarioModel;

class LoginInicialCasoUso
{
    /**
     * Valida credenciales y devuelve el usuario con sus empresas asignadas.
     * No emite token - el token se emite al seleccionar la empresa.
     *
     * @param  string  $email
     * @param  string  $password
     * @return array{usuario: array, empresas: array}
     * @throws \Exception Si las credenciales son invalidas.
     */
    public function ejecutar(string $email, string $password): array
    {
        $usuario = UsuarioModel::where('email', $email)->first();

        if (!$usuario) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        if (!password_verify($password, $usuario->password)) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        $empresas = $usuario->empresas()->get(['enterprises.id', 'enterprises.nombre', 'enterprises.ruc', 'enterprises.tradename']);

        return [
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
            ],
            'empresas' => $empresas->toArray(),
        ];
    }
}
