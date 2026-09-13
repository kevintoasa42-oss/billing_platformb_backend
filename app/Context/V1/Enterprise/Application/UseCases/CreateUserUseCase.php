<?php

namespace App\Context\V1\Enterprise\Application\UseCases;

use App\Context\V1\Enterprise\Application\DTOs\UserDTO;
use App\Context\V1\Enterprise\Domain\Models\User;
use App\Context\V1\Enterprise\Domain\Repositories\UserRepositoryInterface;

class CreateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Crea un user.
     *
     * @param  UserDTO  $dto
     * @return UserDTO
     */
    public function ejecutar(UserDTO $dto): UserDTO
    {
        $user = new User(
            name: $dto->name,
            email: $dto->email,
            password: $dto->password,
        );

        $user = $this->userRepository->crear($user);

        return UserDTO::fromArray([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}
