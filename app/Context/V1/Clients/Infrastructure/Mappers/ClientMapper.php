<?php

namespace App\Context\V1\Clients\Infrastructure\Mappers;

use App\Context\V1\Clients\Domain\Mappers\ClientMapperInterface;
use App\Context\V1\Clients\Domain\Models\Client;
use DateTimeInterface;

final class ClientMapper implements ClientMapperInterface
{
    public function toDomain(array $data): Client
    {
        return new Client(
            id: isset($data['id']) ? (int) $data['id'] : null,
            identification_type: $data['identification_type'] ?? null,
            identification_number: $data['identification_number'] ?? null,
            name: $data['name'] ?? null, last_name: $data['last_name'] ?? null,
            status: $data['status'] ?? null, address: $data['address'] ?? null,
            phone: $data['phone'] ?? null, email: $data['email'] ?? null,
            type: $data['type'] ?? null, plates: $data['plates'] ?? null,
            created_at: $this->date($data['created_at'] ?? null),
            updated_at: $this->date($data['updated_at'] ?? null),
            deleted_at: $this->date($data['deleted_at'] ?? null),
        );
    }

    public function toPersistence(Client $client): array
    {
        return [
            'identification_type' => $client->identification_type,
            'identification_number' => $client->identification_number,
            'name' => $client->name, 'last_name' => $client->last_name,
            'status' => $client->status, 'address' => $client->address,
            'phone' => $client->phone, 'email' => $client->email,
            'type' => $client->type, 'plates' => $client->plates,
        ];
    }

    public function toArray(Client $client): array
    {
        return [
            'id' => $client->id, 'identification_type' => $client->identification_type,
            'identification_number' => $client->identification_number, 'name' => $client->name,
            'last_name' => $client->last_name, 'status' => $client->status, 'address' => $client->address,
            'phone' => $client->phone, 'email' => $client->email, 'type' => $client->type,
            'plates' => $client->plates, 'created_at' => $client->created_at,
            'updated_at' => $client->updated_at, 'deleted_at' => $client->deleted_at,
        ];
    }

    private function date(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DateTimeInterface::ATOM) : ($value === null ? null : (string) $value);
    }
}
