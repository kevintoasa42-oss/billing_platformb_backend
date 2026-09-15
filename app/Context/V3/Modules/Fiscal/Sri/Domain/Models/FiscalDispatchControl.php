<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Models;

/**
 * Fiscal dispatch control state for the SRI pipeline.
 * Controls whether the worker is allowed to send documents to the SRI.
 */
final class FiscalDispatchControl
{
    public function __construct(
        public readonly string $environment,
        public readonly string $state,
        public readonly string $scope,
        public readonly int $version,
        public readonly ?string $reasonCode,
        public readonly ?string $reasonDetail,
        public readonly ?string $pausedAt,
        public readonly ?string $updatedAt,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'environment' => $this->environment,
            'state' => $this->state,
            'scope' => $this->scope,
            'version' => $this->version,
            'reason_code' => $this->reasonCode,
            'reason_detail' => $this->reasonDetail,
            'paused_at' => $this->pausedAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
