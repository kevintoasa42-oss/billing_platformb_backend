<?php

namespace App\Context\V1\SriVoucherTypes\Application\DTOs;

use App\Context\V1\SriVoucherTypes\Domain\Models\SriVoucherType;

final class SriVoucherTypeDTO
{
    /** @param string[] $providedFields */
    public function __construct(
        public ?int $id = null,
        public ?string $document = null,
        public ?string $code = null,
        public ?string $sustentation_code = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public bool $retention = false,
        public ?string $created_at = null,
        public ?string $updated_at = null,
        public ?string $deleted_at = null,
        public array $providedFields = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            document: $data['document'] ?? null,
            code: $data['code'] ?? null,
            sustentation_code: $data['sustentation_code'] ?? null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            retention: (bool) ($data['retention'] ?? false),
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
            deleted_at: $data['deleted_at'] ?? null,
            providedFields: array_keys($data),
        );
    }

    public static function fromDomain(SriVoucherType $voucherType): self
    {
        return new self(
            id: $voucherType->id,
            document: $voucherType->document,
            code: $voucherType->code,
            sustentation_code: $voucherType->sustentation_code,
            start_date: $voucherType->start_date,
            end_date: $voucherType->end_date,
            retention: $voucherType->retention,
            created_at: $voucherType->created_at,
            updated_at: $voucherType->updated_at,
            deleted_at: $voucherType->deleted_at,
        );
    }

    public function inputArray(): array
    {
        return array_intersect_key($this->toArray(), array_flip($this->providedFields));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'document' => $this->document,
            'code' => $this->code,
            'sustentation_code' => $this->sustentation_code,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'retention' => $this->retention,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
