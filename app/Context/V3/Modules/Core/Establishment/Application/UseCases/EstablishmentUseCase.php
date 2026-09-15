<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\UseCases;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\BranchCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\BranchUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EstablishmentCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EstablishmentUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EstablishmentRepositoryInterface;

class EstablishmentUseCase
{
    public function __construct(
        private readonly EstablishmentRepositoryInterface $repository,
    ) {}

    public function all(): array
    {
        $establishments = $this->repository->all();

        return array_map(fn (Establishment $e) => $e->toLegacyArray(), $establishments);
    }

    public function find(string $id): ?array
    {
        $establishment = $this->repository->find($id);

        return $establishment?->toLegacyArray();
    }

    public function create(EstablishmentCreateDTO $dto): array
    {
        $establishment = Establishment::fromArray($dto->toArray());

        $created = $this->repository->create($establishment);

        return $created->toLegacyArray();
    }

    public function update(string $id, EstablishmentUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toLegacyArray(), $dto->toArray());

        $establishment = Establishment::fromArray($merged);

        $updated = $this->repository->update($id, $establishment);

        return $updated?->toLegacyArray();
    }

    /**
     * Branch surface — list branches with nested issuance points.
     */
    public function allBranches(): array
    {
        $branches = $this->repository->allBranches();

        return array_map(fn (Establishment $e) => $e->toArray(), $branches);
    }

    public function createBranch(BranchCreateDTO $dto): ?array
    {
        if ($this->repository->sriCodeExists($dto->sriCode())) {
            throw new \DomainException('El código de establecimiento ya está registrado.');
        }

        $branch = $this->repository->createBranch([
            'sri_code' => $dto->sriCode(),
            'branch_code' => $dto->branchCode ?? $dto->sriCode(),
            'name' => $dto->name,
            'address' => $dto->address,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'city_id' => $dto->cityId,
            'issuance_point' => $dto->issuancePoint,
        ]);

        return $branch?->toArray();
    }

    public function updateBranch(int $legacyId, BranchUpdateDTO $dto): ?array
    {
        $branch = $this->repository->updateBranch($legacyId, [
            'name' => $dto->name,
            'branch_code' => $dto->branchCode,
            'address' => $dto->address,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'city_id' => $dto->cityId,
            'is_active' => $dto->isActive,
            'issuance_point' => $dto->issuancePoint,
        ]);

        return $branch?->toArray();
    }

    public function deleteBranch(int $legacyId): bool
    {
        return $this->repository->deleteByLegacyId($legacyId);
    }
}
