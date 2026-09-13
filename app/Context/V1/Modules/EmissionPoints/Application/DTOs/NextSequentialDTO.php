<?php

namespace App\Context\V1\Modules\EmissionPoints\Application\DTOs;

final class NextSequentialDTO
{
    public function __construct(
        public int $branch_office_id,
        public ?int $emission_point_id,
        public ?string $emission_point,
        public ?int $carrier_id,
        public string $document_code,
        public string $document_label,
        public int $sequential,
        public string $formatted_sequential,
    ) {}

    public function toArray(): array
    {
        return [
            'branch_office_id' => $this->branch_office_id,
            'emission_point_id' => $this->emission_point_id,
            'emission_point' => $this->emission_point,
            'carrier_id' => $this->carrier_id,
            'document_code' => $this->document_code,
            'document_label' => $this->document_label,
            'sequential' => $this->sequential,
            'formatted_sequential' => $this->formatted_sequential,
        ];
    }
}
