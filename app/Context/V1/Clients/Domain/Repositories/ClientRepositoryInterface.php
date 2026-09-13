<?php

namespace App\Context\V1\Clients\Domain\Repositories;

use App\Context\V1\Clients\Domain\Models\Client;

interface ClientRepositoryInterface
{
    /** @return array{data: Client[], total: int, page: int, perPage: int, lastPage: int} */
    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array;

    public function findById(int $id): ?Client;

    public function create(Client $client): Client;

    public function update(Client $client): Client;

    public function delete(int $id): bool;
}
