<?php

namespace App\Context\V3\Modules\Core\SriIva\Application\UseCases;

use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaPercentageCreateDTO;
use App\Context\V3\Modules\Core\SriIva\Application\DTOs\SriIvaPercentageUpdateDTO;
use App\Context\V3\Modules\Core\SriIva\Domain\Models\SriIvaPercentage;
use App\Context\V3\Modules\Core\SriIva\Domain\Repository\SriIvaPercentageRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SriIvaPercentageUseCase
{
    public function __construct(
        private readonly SriIvaPercentageRepositoryInterface $repository,
    ) {}

    /**
     * @return SriIvaPercentage[]
     */
    public function findByType(int $sriIvaTypeId): array
    {
        return $this->repository->findByType($sriIvaTypeId);
    }

    public function find(int $id): ?SriIvaPercentage
    {
        return $this->repository->find($id);
    }

    public function create(SriIvaPercentageCreateDTO $dto): SriIvaPercentage
    {
        return DB::connection('master_v3')->transaction(
            fn (): SriIvaPercentage => $this->repository->create($dto->toArray())
        );
    }

    public function update(int $id, SriIvaPercentageUpdateDTO $dto): ?SriIvaPercentage
    {
        return DB::connection('master_v3')->transaction(
            fn (): ?SriIvaPercentage => $this->repository->update($id, $dto->toArray())
        );
    }

    public function delete(int $id): bool
    {
        return DB::connection('master_v3')->transaction(
            fn (): bool => $this->repository->delete($id)
        );
    }
}
