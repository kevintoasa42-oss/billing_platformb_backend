<?php

namespace App\Context\V3\Modules\Core\Establishment\Application\UseCases;

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

        return array_map(fn (Establishment $e) => $e->toArray(), $establishments);
    }

    public function find(string $id): ?array
    {
        $establishment = $this->repository->find($id);

        return $establishment?->toArray();
    }

    public function create(EstablishmentCreateDTO $dto): array
    {
        $establishment = Establishment::fromArray($dto->toArray());

        $created = $this->repository->create($establishment);

        return $created->toArray();
    }

    public function update(string $id, EstablishmentUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $dto->toArray());

        $establishment = Establishment::fromArray($merged);

        $updated = $this->repository->update($id, $establishment);

        return $updated?->toArray();
    }
}
