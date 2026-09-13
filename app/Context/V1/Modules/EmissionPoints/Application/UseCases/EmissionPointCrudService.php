<?php

namespace App\Context\V1\Modules\EmissionPoints\Application\UseCases;

use App\Context\V1\Modules\EmissionPoints\Application\DTOs\EmissionPointDTO;
use App\Context\V1\Modules\EmissionPoints\Domain\Exceptions\EmissionPointNotFoundException;
use App\Context\V1\Modules\EmissionPoints\Domain\Mappers\EmissionPointMapperInterface;
use App\Context\V1\Modules\EmissionPoints\Domain\Models\EmissionPoint;
use App\Context\V1\Modules\EmissionPoints\Domain\Repositories\EmissionPointRepositoryInterface;

final readonly class EmissionPointCrudService
{
    public function __construct(
        private EmissionPointRepositoryInterface $repository,
        private EmissionPointMapperInterface     $mapper,
    )
    {
    }

    public function list(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $result = $this->repository->listPaginated($page, $perPage, $filters);
        $result['data'] = array_map(fn(EmissionPoint $point) => EmissionPointDTO::fromDomain($point)->toArray(), $result['data']);

        return $result;
    }

    public function get(int $id): ?EmissionPointDTO
    {
        $point = $this->repository->findById($id);

        return $point ? EmissionPointDTO::fromDomain($point) : null;
    }

    public function create(EmissionPointDTO $dto): EmissionPointDTO
    {
        return EmissionPointDTO::fromDomain($this->repository->create($this->mapper->toDomain($dto->toArray())));
    }

    public function update(EmissionPointDTO $dto): EmissionPointDTO
    {
        $existing = $this->repository->findById((int)$dto->id);
        if (!$existing) {
            throw new EmissionPointNotFoundException((int)($dto->branch_office_id ?? 0), $dto->id);
        }
        $point = $this->mapper->toDomain(array_merge($this->mapper->toArray($existing), $dto->inputArray()));

        return EmissionPointDTO::fromDomain($this->repository->update($point));
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
