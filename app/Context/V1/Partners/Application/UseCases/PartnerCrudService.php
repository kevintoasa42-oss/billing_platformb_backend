<?php

namespace App\Context\V1\Partners\Application\UseCases;

use App\Context\V1\Partners\Application\DTOs\PartnerDTO;
use App\Context\V1\Partners\Domain\Exceptions\PartnerNotFoundException;
use App\Context\V1\Partners\Domain\Mappers\PartnerMapperInterface;
use App\Context\V1\Partners\Domain\Models\Partner;
use App\Context\V1\Partners\Domain\Repositories\PartnerRepositoryInterface;

final readonly class PartnerCrudService
{
    public function __construct(
        private PartnerRepositoryInterface $repository,
        private PartnerMapperInterface $mapper,
    ) {}

    /** @param array<string, mixed> $filters */
    public function list(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $result = $this->repository->listPaginated($page, $perPage, $filters);
        $result['data'] = array_map(
            static fn (Partner $partner) => PartnerDTO::fromDomain($partner)->toArray(),
            $result['data'],
        );

        return $result;
    }

    public function get(int $id): ?PartnerDTO
    {
        $partner = $this->repository->findById($id);

        return $partner ? PartnerDTO::fromDomain($partner) : null;
    }

    public function create(PartnerDTO $dto): PartnerDTO
    {
        return PartnerDTO::fromDomain($this->repository->create($this->mapper->toDomain($dto->toArray())));
    }

    public function update(PartnerDTO $dto): PartnerDTO
    {
        $existing = $this->repository->findById((int) $dto->id);

        if (! $existing) {
            throw new PartnerNotFoundException((int) $dto->id);
        }

        $partner = $this->mapper->toDomain(array_merge($this->mapper->toArray($existing), $dto->inputArray()));

        return PartnerDTO::fromDomain($this->repository->update($partner));
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
