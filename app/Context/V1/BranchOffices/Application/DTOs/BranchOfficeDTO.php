<?php

namespace App\Context\V1\BranchOffices\Application\DTOs;

use App\Context\V1\BranchOffices\Domain\Models\BranchOffice;

final class BranchOfficeDTO
{
    /** @param string[] $providedFields */
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $code_sri = null,
        public bool $status = false,
        public ?string $type = null,
        public bool $default = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
        public array $providedFields = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            name: $data['name'] ?? null,
            code_sri: $data['code_sri'] ?? null,
            status: (bool) ($data['status'] ?? false),
            type: $data['type'] ?? null,
            default: (bool) ($data['default'] ?? false),
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
            deleted_at: $data['deleted_at'] ?? null,
            providedFields: array_keys($data),
        );
    }

    public static function fromDomain(BranchOffice $branchOffice): self
    {
        return new self(
            id: $branchOffice->id,
            name: $branchOffice->name,
            code_sri: $branchOffice->code_sri,
            status: $branchOffice->status,
            type: $branchOffice->type,
            default: $branchOffice->default,
            created_at: $branchOffice->created_at,
            updated_at: $branchOffice->updated_at,
            deleted_at: $branchOffice->deleted_at,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id, 'name' => $this->name, 'code_sri' => $this->code_sri,
            'status' => $this->status, 'type' => $this->type, 'default' => $this->default,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at, 'deleted_at' => $this->deleted_at,
        ];
    }

    public function inputArray(): array
    {
        return array_intersect_key($this->toArray(), array_flip($this->providedFields));
    }
}
