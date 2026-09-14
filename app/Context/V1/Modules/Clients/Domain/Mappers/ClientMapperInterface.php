<?php

namespace App\Context\V1\Modules\Clients\Domain\Mappers;

use App\Context\V1\Modules\Clients\Domain\Models\Client;

interface ClientMapperInterface
{
    public function toDomain(array $data): Client;

    public function toPersistence(Client $client): array;

    public function toArray(Client $client): array;
}
