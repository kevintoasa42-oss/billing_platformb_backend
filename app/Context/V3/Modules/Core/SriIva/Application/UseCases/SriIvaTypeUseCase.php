<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\UseCases;

use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaTypeCreateDTO;
use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaTypeUpdateDTO;
use App\Context\V3\Modules\Core\SriIva\Domain\Models\SriIvaType;
use App\Context\V3\Modules\Core\SriIva\Domain\Repository\SriIvaTypeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SriIvaTypeUseCase
{
    public function __construct(
        private readonly SriIvaTypeRepositoryInterface $repository,
    ) {}

    /**
     * @return SriIvaType[]
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    public function find(int $id): ?SriIvaType
    {
        return $this->repository->find($id);
    }

    public function create(SriIvaTypeCreateDTO $dto): SriIvaType
    {
        return DB::connection('master_v3')->transaction(
            fn (): SriIvaType => $this->repository->create($dto->toArray())
        );
    }

    public function update(int $id, SriIvaTypeUpdateDTO $dto): ?SriIvaType
    {
        return DB::connection('master_v3')->transaction(
            fn (): ?SriIvaType => $this->repository->update($id, $dto->toArray())
        );
    }

    public function delete(int $id): bool
    {
        return DB::connection('master_v3')->transaction(
            fn (): bool => $this->repository->delete($id)
        );
    }
}
