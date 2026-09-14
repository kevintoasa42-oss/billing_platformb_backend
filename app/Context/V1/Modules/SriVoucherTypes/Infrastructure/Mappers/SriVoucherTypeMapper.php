<?php

namespace App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Mappers;

use App\Context\V1\Modules\SriVoucherTypes\Domain\Mappers\SriVoucherTypeMapperInterface;
use App\Context\V1\Modules\SriVoucherTypes\Domain\Models\SriVoucherType;
use DateTimeInterface;

final class SriVoucherTypeMapper implements SriVoucherTypeMapperInterface
{
    public function toDomain(array $data): SriVoucherType
    {
        return new SriVoucherType(
            id: isset($data['id']) ? (int) $data['id'] : null,
            document: $data['document'] ?? null,
            code: $data['code'] ?? null,
            sustentation_code: $data['sustentation_code'] ?? null,
            start_date: $this->date($data['start_date'] ?? null),
            end_date: $this->date($data['end_date'] ?? null),
            retention: (bool) ($data['retention'] ?? false),
            created_at: $this->dateTime($data['created_at'] ?? null),
            updated_at: $this->dateTime($data['updated_at'] ?? null),
            deleted_at: $this->dateTime($data['deleted_at'] ?? null),
        );
    }

    public function toPersistence(SriVoucherType $voucherType): array
    {
        return [
            'document' => $voucherType->document,
            'code' => $voucherType->code,
            'sustentation_code' => $voucherType->sustentation_code,
            'start_date' => $voucherType->start_date,
            'end_date' => $voucherType->end_date,
            'retention' => $voucherType->retention,
        ];
    }

    public function toArray(SriVoucherType $voucherType): array
    {
        return [
            'id' => $voucherType->id,
            'document' => $voucherType->document,
            'code' => $voucherType->code,
            'sustentation_code' => $voucherType->sustentation_code,
            'start_date' => $voucherType->start_date,
            'end_date' => $voucherType->end_date,
            'retention' => $voucherType->retention,
            'created_at' => $voucherType->created_at,
            'updated_at' => $voucherType->updated_at,
            'deleted_at' => $voucherType->deleted_at,
        ];
    }

    private function date(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface
            ? $value->format('Y-m-d')
            : ($value === null ? null : (string) $value);
    }

    private function dateTime(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface
            ? $value->format(DateTimeInterface::ATOM)
            : ($value === null ? null : (string) $value);
    }
}
