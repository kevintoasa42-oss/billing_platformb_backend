<?php

namespace App\Context\V1\Clients\Application\DTOs;

use App\Context\V1\Clients\Domain\Models\Client;

final class ClientDTO
{
    /** @param string[] $providedFields */
    public function __construct(
        public ?int    $id = null,
        public ?string $identification_type = null,
        public ?string $identification_number = null,
        public ?string $name = null,
        public ?string $last_name = null,
        public ?string $status = null,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $type = null,
        public ?string $plates = null,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
        public array   $providedFields = [],
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            identification_type: $data['identification_type'] ?? null,
            identification_number: $data['identification_number'] ?? null,
            name: $data['name'] ?? null, last_name: $data['last_name'] ?? null,
            status: $data['status'] ?? null, address: $data['address'] ?? null,
            phone: $data['phone'] ?? null, email: $data['email'] ?? null,
            type: $data['type'] ?? null, plates: $data['plates'] ?? null,
            created_at: $data['created_at'] ?? null, updated_at: $data['updated_at'] ?? null,
            deleted_at: $data['deleted_at'] ?? null, providedFields: array_keys($data),
        );
    }

    public static function fromDomain(Client $client): self
    {
        return new self(
            id: $client->id, identification_type: $client->identification_type,
            identification_number: $client->identification_number, name: $client->name,
            last_name: $client->last_name, status: $client->status, address: $client->address,
            phone: $client->phone, email: $client->email, type: $client->type,
            plates: $client->plates, created_at: $client->created_at,
            updated_at: $client->updated_at, deleted_at: $client->deleted_at,
        );
    }

    public function inputArray(): array
    {
        return array_intersect_key($this->toArray(), array_flip($this->providedFields));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id, 'identification_type' => $this->identification_type,
            'identification_number' => $this->identification_number, 'name' => $this->name,
            'last_name' => $this->last_name, 'status' => $this->status, 'address' => $this->address,
            'phone' => $this->phone, 'email' => $this->email, 'type' => $this->type,
            'plates' => $this->plates, 'created_at' => $this->created_at,
            'updated_at' => $this->updated_at, 'deleted_at' => $this->deleted_at,
        ];
    }
}
