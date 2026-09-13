<?php

namespace App\Context\V1\Modules\BranchOffices\Application\UseCases;

use App\Context\V1\Modules\BranchOffices\Application\DTOs\BranchOfficeDTO;
use App\Context\V1\Modules\BranchOffices\Domain\Exceptions\BranchOfficeNotFoundException;
use App\Context\V1\Modules\BranchOffices\Domain\Mappers\BranchOfficeMapperInterface;
use App\Context\V1\Modules\BranchOffices\Domain\Models\BranchOffice;
use App\Context\V1\Modules\BranchOffices\Domain\Repositories\BranchOfficeRepositoryInterface;

final readonly class BranchOfficeCrudService
{
    public function __construct(
        private BranchOfficeRepositoryInterface $repository,
        private BranchOfficeMapperInterface     $mapper,
    )
    {
    }

    public function list(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $result = $this->repository->listPaginated($page, $perPage, $filters);
        $result['data'] = array_map(
            fn(BranchOffice $branchOffice) => BranchOfficeDTO::fromDomain($branchOffice)->toArray(),
            $result['data'],
        );

        return $result;
    }

    public function get(int $id): ?BranchOfficeDTO
    {
        $branchOffice = $this->repository->findById($id);

        return $branchOffice ? BranchOfficeDTO::fromDomain($branchOffice) : null;
    }

    public function create(BranchOfficeDTO $dto): BranchOfficeDTO
    {
        return BranchOfficeDTO::fromDomain(
            $this->repository->create($this->mapper->toDomain($dto->toArray()))
        );
    }

    public function update(BranchOfficeDTO $dto): BranchOfficeDTO
    {
        $existing = $this->repository->findById((int)$dto->id);
        if (!$existing) {
            throw new BranchOfficeNotFoundException((int)$dto->id);
        }

        $branchOffice = $this->mapper->toDomain(
            array_merge($this->mapper->toArray($existing), $dto->inputArray())
        );

        return BranchOfficeDTO::fromDomain($this->repository->update($branchOffice));
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
