<?php

namespace App\Context\V1\Modules\EmissionPoints\Application\DTOs;

use App\Context\V1\Modules\EmissionPoints\Domain\Models\EmissionPoint;

final class EmissionPointDTO
{
    /** @param string[] $providedFields */
    public function __construct(
        public ?int    $id = null,
        public ?int    $branch_office_id = null,
        public ?string $name = null,
        public ?string $emission_point = null,
        public bool    $status = false,
        public bool    $default = false,
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
            branch_office_id: isset($data['branch_office_id']) ? (int)$data['branch_office_id'] : null,
            name: $data['name'] ?? null, emission_point: $data['emission_point'] ?? null,
            status: (bool)($data['status'] ?? false), default: (bool)($data['default'] ?? false),
            created_at: $data['created_at'] ?? null, updated_at: $data['updated_at'] ?? null,
            deleted_at: $data['deleted_at'] ?? null, providedFields: array_keys($data),
        );
    }

    public static function fromDomain(EmissionPoint $emissionPoint): self
    {
        return new self(
            id: $emissionPoint->id, branch_office_id: $emissionPoint->branch_office_id,
            name: $emissionPoint->name, emission_point: $emissionPoint->emission_point,
            status: $emissionPoint->status, default: $emissionPoint->default,
            created_at: $emissionPoint->created_at, updated_at: $emissionPoint->updated_at,
            deleted_at: $emissionPoint->deleted_at,
        );
    }

    public function inputArray(): array
    {
        return array_intersect_key($this->toArray(), array_flip($this->providedFields));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id, 'branch_office_id' => $this->branch_office_id,
            'name' => $this->name, 'emission_point' => $this->emission_point,
            'status' => $this->status, 'default' => $this->default,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
