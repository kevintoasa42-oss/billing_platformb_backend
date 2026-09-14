<?php

namespace App\Context\V1\Modules\Clients\Application\UseCases;

use App\Context\V1\Modules\Clients\Application\DTOs\ClientDTO;
use App\Context\V1\Modules\Clients\Domain\Exceptions\ClientNotFoundException;
use App\Context\V1\Modules\Clients\Domain\Mappers\ClientMapperInterface;
use App\Context\V1\Modules\Clients\Domain\Models\Client;
use App\Context\V1\Modules\Clients\Domain\Repositories\ClientRepositoryInterface;

final readonly class ClientCrudService
{
    public function __construct(
        private ClientRepositoryInterface $repository,
        private ClientMapperInterface     $mapper,
    )
    {
    }

    public function list(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $result = $this->repository->listPaginated($page, $perPage, $filters);
        $result['data'] = array_map(fn(Client $client) => ClientDTO::fromDomain($client)->toArray(), $result['data']);

        return $result;
    }

    public function get(int $id): ?ClientDTO
    {
        $client = $this->repository->findById($id);

        return $client ? ClientDTO::fromDomain($client) : null;
    }

    public function create(ClientDTO $dto): ClientDTO
    {
        return ClientDTO::fromDomain($this->repository->create($this->mapper->toDomain($dto->toArray())));
    }

    public function update(ClientDTO $dto): ClientDTO
    {
        $existing = $this->repository->findById((int)$dto->id);
        if (!$existing) {
            throw new ClientNotFoundException((int)$dto->id);
        }
        $client = $this->mapper->toDomain(array_merge($this->mapper->toArray($existing), $dto->inputArray()));

        return ClientDTO::fromDomain($this->repository->update($client));
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
