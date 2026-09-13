<?php

namespace App\Context\V1\Enterprise\Application\UseCases;

use App\Context\V1\Enterprise\Application\DTOs\LoginDTO;
use App\Context\V1\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface;
use App\Context\V1\Enterprise\Domain\Repositories\UserRepositoryInterface;
use App\Context\V1\Enterprise\Infrastructure\Eloquent\Models\UserModel;

class LoginUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EnterpriseRepositoryInterface $enterpriseRepository,
    ) {}

    /**
     * Valida credenciales, selecciona la enterprise y emite un token Sanctum
     * con el enterprise_id correspondiente.
     *
     * @param  LoginDTO  $dto
     * @return array{token: string, user: array, enterprise: array}
     * @throws \Exception Si las credenciales son inválidas o la enterprise no pertenece al user.
     */
    public function ejecutar(LoginDTO $dto): array
    {
        // Buscar user por email.
        $userModel = UserModel::where('email', $dto->email)->first();

        if (!$userModel) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        // Validar password.
        if (!password_verify($dto->password, $userModel->password)) {
            throw new \Exception('Credenciales inválidas.', 401);
        }

        // Validar que la enterprise pertenece al user.
        $enterprise = $userModel->enterprises()->where('enterprise_id', $dto->enterprise_id)->first();

        if (!$enterprise) {
            throw new \Exception('La enterprise seleccionada no pertenece al user.', 403);
        }

        // Emitir token Sanctum con el enterprise_id.
        $token = $userModel->createToken(
            name: 'auth_token',
            abilities: ['*'],
        );

        // Asignar enterprise_id al token.
        $token->accessToken->forceFill([
            'enterprise_id' => $dto->enterprise_id,
        ])->save();

        return [
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $userModel->id,
                'name' => $userModel->name,
                'email' => $userModel->email,
            ],
            'enterprise' => [
                'id' => $enterprise->id,
                'name' => $enterprise->name,
                'ruc' => $enterprise->ruc,
            ],
        ];
    }
}
