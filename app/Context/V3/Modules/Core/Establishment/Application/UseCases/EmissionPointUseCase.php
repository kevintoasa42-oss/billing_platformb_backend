<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\UseCases;

use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EmissionPointCreateDTO;
use App\Context\V3\Modules\Core\Establishment\Application\DTOs\EmissionPointUpdateDTO;
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

        return array_map(fn (EmissionPoint $e) => $e->toArray(), $emissionPoints);
    }

    public function find(string $id): ?array
    {
        $emissionPoint = $this->repository->find($id);

        return $emissionPoint?->toArray();
    }

    public function byEstablishment(string $establishmentId): array
    {
        $emissionPoints = $this->repository->byEstablishment($establishmentId);

        return array_map(fn (EmissionPoint $e) => $e->toArray(), $emissionPoints);
    }

    public function create(EmissionPointCreateDTO $dto): array
    {
        $emissionPoint = EmissionPoint::fromArray($dto->toArray());

        $created = $this->repository->create($emissionPoint);

        return $created->toArray();
    }

    public function update(string $id, EmissionPointUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $dto->toArray());

        $emissionPoint = EmissionPoint::fromArray($merged);

        $updated = $this->repository->update($id, $emissionPoint);

        return $updated?->toArray();
    }
}
