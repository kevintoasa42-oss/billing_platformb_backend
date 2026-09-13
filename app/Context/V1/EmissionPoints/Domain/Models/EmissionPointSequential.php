<?php

namespace App\Context\V1\EmissionPoints\Domain\Models;

/** Framework-independent sequential reserved for an emission point. */
final class EmissionPointSequential
{
    public function __construct(
        public string $branch_office_code_sri,
        public string $emission_point,
        public int $sequential,
    ) {}

    public function formatted(): string
    {
        return sprintf(
            '%s-%s-%s',
            $this->branch_office_code_sri,
            $this->emission_point,
            str_pad((string) $this->sequential, 9, '0', STR_PAD_LEFT),
        );
    }
}
