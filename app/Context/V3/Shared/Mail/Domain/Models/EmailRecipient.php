<?php

namespace App\Context\V3\Shared\Mail\Domain\Models;

class EmailRecipient
{
    public function __construct(
        public readonly string $address,
        public readonly ?string $name = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            address: $data['address'],
            name: $data['name'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address,
            'name' => $this->name,
        ];
    }
}
