<?php

namespace App\Context\V1\Carrier\Application\DTOs;

class CarrierDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $ruc = null,
        public ?string $name = null,
        public ?string $tradename = null,
        public ?string $matrix_address = null,
        public ?string $special_taxpayer = null,
        public bool $accounting_required = false,
        public bool $status = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            ruc: $data['ruc'] ?? null,
            name: $data['name'] ?? null,
            tradename: $data['tradename'] ?? null,
            matrix_address: $data['matrix_address'] ?? null,
            special_taxpayer: $data['special_taxpayer'] ?? null,
            accounting_required: $data['accounting_required'] ?? false,
            status: $data['status'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ruc' => $this->ruc,
            'name' => $this->name,
            'tradename' => $this->tradename,
            'matrix_address' => $this->matrix_address,
            'special_taxpayer' => $this->special_taxpayer,
            'accounting_required' => $this->accounting_required,
            'status' => $this->status,
        ];
    }
}
