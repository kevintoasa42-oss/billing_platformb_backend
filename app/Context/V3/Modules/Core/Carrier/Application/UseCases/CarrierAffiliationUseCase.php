<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\UseCases;

use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierAffiliationCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierAffiliationUpdateDTO;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierAffiliation;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierAffiliationRepositoryInterface;

class CarrierAffiliationUseCase
{
    public function __construct(
        private readonly CarrierAffiliationRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $affiliations = $this->repository->all();

        return array_map(fn (CarrierAffiliation $a) => $a->toArray(), $affiliations);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $affiliation = $this->repository->find($id);

        return $affiliation?->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CarrierAffiliationCreateDTO $dto): array
    {
        $affiliation = CarrierAffiliation::fromArray($dto->toArray());

        $created = $this->repository->create($affiliation);

        return $created->toArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function update(string $id, CarrierAffiliationUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $dto->toArray());

        $affiliation = CarrierAffiliation::fromArray($merged);

        $updated = $this->repository->update($id, $affiliation);

        return $updated?->toArray();
    }
}
