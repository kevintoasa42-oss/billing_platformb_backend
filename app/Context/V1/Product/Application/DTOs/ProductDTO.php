<?php

namespace App\Context\V1\Product\Application\DTOs;

class ProductDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $barcode = null,
        public ?string $auxiliary_code = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool $status = true,
        public float $base_price = 0,
        /** @var int[] */
        public array $taxes = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            barcode: $data['barcode'] ?? null,
            auxiliary_code: $data['auxiliary_code'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            status: $data['status'] ?? true,
            base_price: $data['base_price'] ?? 0,
            taxes: $data['taxes'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'barcode' => $this->barcode,
            'auxiliary_code' => $this->auxiliary_code,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'base_price' => $this->base_price,
            'taxes' => $this->taxes,
        ];
    }
}
