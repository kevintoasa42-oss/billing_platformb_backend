<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\UseCases;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EmissionPointCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EmissionPointUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\IssuancePointCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\IssuancePointUpdateDTO;
use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EmissionPointRepositoryInterface;

class EmissionPointUseCase
{
    public function __construct(
        private readonly EmissionPointRepositoryInterface $repository,
    ) {}

    public function all(): array
    {
        $emissionPoints = $this->repository->all();

        return array_map(fn (EmissionPoint $e) => $e->toLegacyArray(), $emissionPoints);
    }

    public function find(string $id): ?array
    {
        $emissionPoint = $this->repository->find($id);

        return $emissionPoint?->toLegacyArray();
    }

    public function byEstablishment(string $establishmentId): array
    {
        $emissionPoints = $this->repository->byEstablishment($establishmentId);

        return array_map(fn (EmissionPoint $e) => $e->toLegacyArray(), $emissionPoints);
    }

    public function create(EmissionPointCreateDTO $dto): array
    {
        $emissionPoint = EmissionPoint::fromArray($dto->toArray());

        $created = $this->repository->create($emissionPoint);

        return $created->toLegacyArray();
    }

    public function update(string $id, EmissionPointUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toLegacyArray(), $dto->toArray());

        $emissionPoint = EmissionPoint::fromArray($merged);

        $updated = $this->repository->update($id, $emissionPoint);

        return $updated?->toLegacyArray();
    }

    /**
     * Issuance-point surface — legacy_id based operations.
     */
    public function byBranchLegacyId(int $branchLegacyId): array
    {
        $points = $this->repository->byBranchLegacyId($branchLegacyId);

        return array_map(fn (EmissionPoint $e) => $e->toArray(), $points);
    }

    public function createForBranch(int $branchLegacyId, IssuancePointCreateDTO $dto): ?array
    {
        $point = $this->repository->createForBranch($branchLegacyId, $dto->toArray());

        return $point?->toArray();
    }

    public function updateByLegacyId(int $legacyId, IssuancePointUpdateDTO $dto): ?array
    {
        $point = $this->repository->updateByLegacyId($legacyId, $dto->toArray());

        return $point?->toArray();
    }

    public function deleteByLegacyId(int $legacyId): bool
    {
        return $this->repository->deleteByLegacyId($legacyId);
    }

    /**
     * @return array{sequential_number: int, sequential: string, document_number: string}|null
     */
    public function nextSequential(int $branchLegacyId, int $pointLegacyId): ?array
    {
        return $this->repository->nextSequential($branchLegacyId, $pointLegacyId);
    }
}
