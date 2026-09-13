<?php

namespace App\Context\V1\EmissionPoints\Application\DTOs;

final class NextSequentialDTO
{
    public function __construct(
        public int $branch_office_id,
        public ?int $emission_point_id,
        public ?string $emission_point,
        public int $sequential,
    ) {}

    public function toArray(): array
    {
        return [
            'branch_office_id' => $this->branch_office_id,
            'emission_point_id' => $this->emission_point_id,
            'emission_point' => $this->emission_point,
            'sequential' => $this->sequential,
        ];
    }
}
