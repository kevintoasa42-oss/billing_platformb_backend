<?php

namespace App\Context\V1\Carrier\Domain\Repositories;

use App\Context\V1\Carrier\Domain\Models\Carrier;

interface CarrierRepositoryInterface
{
    /**
     * Paginated list of carriers.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array{data: Carrier[], total: int, page: int, perPage: int, lastPage: int}
     */
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array;

    public function getById(int $id): ?array;

    public function create(Carrier $carrier): array;

    public function update(Carrier $carrier): array;

    public function changeStatus(int $id, bool $status): bool;
}
