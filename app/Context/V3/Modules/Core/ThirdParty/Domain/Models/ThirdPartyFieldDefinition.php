<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Models;

/**
 * Tenant-scoped definition for a configurable third-party field.
 *
 * @param  array<string, mixed>  $validation
 */
final readonly class ThirdPartyFieldDefinition
{
    public function __construct(
        public string $id,
        public string $code,
        public string $label,
        public string $scope,
        public string $dataType,
        public array $validation,
        public int $sortOrder,
        public bool $isRequired,
        public bool $isActive,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'scope' => $this->scope,
            'data_type' => $this->dataType,
            'validation' => $this->validation,
            'sort_order' => $this->sortOrder,
            'is_required' => $this->isRequired,
            'is_active' => $this->isActive,
        ];
    }
}
