<?php

namespace App\Context\V1\EmissionPoints\Domain\Repositories;

use App\Context\V1\EmissionPoints\Domain\Models\EmissionPoint;

interface EmissionPointRepositoryInterface
{
    /** @return array{data: EmissionPoint[], total: int, page: int, perPage: int, lastPage: int} */
    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array;

    public function findById(int $id): ?EmissionPoint;

    public function create(EmissionPoint $emissionPoint): EmissionPoint;

    public function update(EmissionPoint $emissionPoint): EmissionPoint;

    public function delete(int $id): bool;
}
