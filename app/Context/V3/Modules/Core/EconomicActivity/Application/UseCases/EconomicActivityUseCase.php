<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Application\UseCases;

use App\Context\V3\Modules\Core\EconomicActivity\Application\DTOs\EconomicActivityCreateDTO;
use App\Context\V3\Modules\Core\EconomicActivity\Application\DTOs\EconomicActivityUpdateDTO;
use App\Context\V3\Modules\Core\EconomicActivity\Domain\Models\EconomicActivity;
use App\Context\V3\Modules\Core\EconomicActivity\Domain\Repository\EconomicActivityRepositoryInterface;

class EconomicActivityUseCase
{
    public function __construct(
        private readonly EconomicActivityRepositoryInterface $repository,
    ) {}

    public function all(): array
    {
        $activities = $this->repository->all();

        return array_map(fn (EconomicActivity $a) => $a->toArray(), $activities);
    }

    public function find(string $id): ?array
    {
        $activity = $this->repository->find($id);

        return $activity?->toArray();
    }

    public function create(EconomicActivityCreateDTO $dto): array
    {
        $activity = EconomicActivity::fromArray($dto->toArray());

        $created = $this->repository->create($activity);

        return $created->toArray();
    }

    public function update(string $id, EconomicActivityUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $dto->toArray());

        $activity = EconomicActivity::fromArray($merged);

        $updated = $this->repository->update($id, $activity);

        return $updated?->toArray();
    }
}
