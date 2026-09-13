<?php

namespace App\Context\Enterprise\Application\UseCases;

use App\Context\Enterprise\Infrastructure\Eloquent\Models\UserModel;

class InitialLoginUseCase
{
    /**
     * Valida credenciales y devuelve el user con sus enterprises asignadas.
     * No emite token - el token se emite al seleccionar la enterprise.
     *
     * @param  string  $email
     * @param  string  $password
     * @return array{user: array, enterprises: array}
     * @throws \Exception Si las credenciales son invalidas.
     */
    public function ejecutar(string $email, string $password): array
    {
        $user = UserModel::where('email', $email)->first();

        if (!$user) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        if (!password_verify($password, $user->password)) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        $enterprises = $user->enterprises()->get(['enterprises.id', 'enterprises.nombre', 'enterprises.ruc', 'enterprises.tradename']);

        return [
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
            ],
            'enterprises' => $enterprises->toArray(),
        ];
    }
}
