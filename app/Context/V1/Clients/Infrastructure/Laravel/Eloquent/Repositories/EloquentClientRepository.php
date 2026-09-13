<?php

namespace App\Context\V1\Clients\Infrastructure\Laravel\Eloquent\Repositories;

use App\Context\V1\Clients\Domain\Mappers\ClientMapperInterface;
use App\Context\V1\Clients\Domain\Models\Client;
use App\Context\V1\Clients\Domain\Repositories\ClientRepositoryInterface;
use App\Context\V1\Clients\Infrastructure\Laravel\Eloquent\Models\ClientModel;

final class EloquentClientRepository implements ClientRepositoryInterface
{
    public function __construct(private ClientMapperInterface $mapper) {}

    public function listPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $query = ClientModel::query();
        if (! empty($filters['search'])) {
            $value = '%'.$filters['search'].'%';
            $query->where(fn ($q) => $q->where('identification_number', 'like', $value)->orWhere('name', 'like', $value)->orWhere('last_name', 'like', $value)->orWhere('email', 'like', $value));
        }
        foreach (['status', 'type', 'identification_type'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->getCollection()->map(fn (ClientModel $model) => $this->mapper->toDomain($model->toArray()))->all(),
            'total' => $paginator->total(), 'page' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(), 'lastPage' => $paginator->lastPage(),
        ];
    }

    public function findById(int $id): ?Client
    {
        $model = ClientModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function create(Client $client): Client
    {
        $model = ClientModel::create($this->mapper->toPersistence($client));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function update(Client $client): Client
    {
        $model = ClientModel::findOrFail($client->id);
        $model->update($this->mapper->toPersistence($client));

        return $this->mapper->toDomain($model->fresh()->toArray());
    }

    public function delete(int $id): bool
    {
        return ClientModel::find($id)?->delete() ?? false;
    }
}
