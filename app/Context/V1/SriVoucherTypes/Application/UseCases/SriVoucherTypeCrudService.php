<?php

namespace App\Context\V1\SriVoucherTypes\Application\UseCases;

use App\Context\V1\SriVoucherTypes\Application\DTOs\SriVoucherTypeDTO;
use App\Context\V1\SriVoucherTypes\Domain\Exceptions\SriVoucherTypeNotFoundException;
use App\Context\V1\SriVoucherTypes\Domain\Mappers\SriVoucherTypeMapperInterface;
use App\Context\V1\SriVoucherTypes\Domain\Models\SriVoucherType;
use App\Context\V1\SriVoucherTypes\Domain\Repositories\SriVoucherTypeRepositoryInterface;

final readonly class SriVoucherTypeCrudService
{
    public function __construct(
        private SriVoucherTypeRepositoryInterface $repository,
        private SriVoucherTypeMapperInterface $mapper,
    ) {}

    public function list(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $result = $this->repository->listPaginated($page, $perPage, $filters);
        $result['data'] = array_map(
            fn (SriVoucherType $voucherType) => SriVoucherTypeDTO::fromDomain($voucherType)->toArray(),
            $result['data'],
        );

        return $result;
    }

    public function get(int $id): ?SriVoucherTypeDTO
    {
        $voucherType = $this->repository->findById($id);

        return $voucherType ? SriVoucherTypeDTO::fromDomain($voucherType) : null;
    }

    /** Resolves the currently valid landlord catalogue entry for a SRI code. */
    public function getCurrentByCode(string $code): ?SriVoucherTypeDTO
    {
        $voucherType = $this->repository->findCurrentByCode($code);

        return $voucherType ? SriVoucherTypeDTO::fromDomain($voucherType) : null;
    }

    public function create(SriVoucherTypeDTO $dto): SriVoucherTypeDTO
    {
        return SriVoucherTypeDTO::fromDomain(
            $this->repository->create($this->mapper->toDomain($dto->toArray())),
        );
    }

    public function update(SriVoucherTypeDTO $dto): SriVoucherTypeDTO
    {
        $existing = $this->repository->findById((int) $dto->id);
        if (! $existing) {
            throw new SriVoucherTypeNotFoundException((int) $dto->id);
        }

        $voucherType = $this->mapper->toDomain(
            array_merge($this->mapper->toArray($existing), $dto->inputArray()),
        );

        return SriVoucherTypeDTO::fromDomain($this->repository->update($voucherType));
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
