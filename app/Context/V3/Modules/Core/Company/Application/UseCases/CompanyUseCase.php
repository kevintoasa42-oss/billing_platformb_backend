<?php

namespace App\Context\V3\Modules\Core\Company\Application\UseCases;

use App\Context\V3\Modules\Core\Company\Application\DTOs\CompanyCreateDTO;
use App\Context\V3\Modules\Core\Company\Application\DTOs\CompanyUpdateDTO;
use App\Context\V3\Modules\Core\Company\Domain\Models\Company;
use App\Context\V3\Modules\Core\Company\Domain\Repository\CompanyRepositoryInterface;

class CompanyUseCase
{
    public function __construct(
        private readonly CompanyRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $companies = $this->repository->all();

        return array_map(fn (Company $c) => $c->toArray(), $companies);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $company = $this->repository->find($id);

        return $company?->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CompanyCreateDTO $dto): array
    {
        $company = Company::fromArray($dto->toArray());

        $created = $this->repository->create($company);

        return $created->toArray();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function update(string $id, CompanyUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $dto->toArray());

        $company = Company::fromArray($merged);

        $updated = $this->repository->update($id, $company);

        return $updated?->toArray();
    }
}
