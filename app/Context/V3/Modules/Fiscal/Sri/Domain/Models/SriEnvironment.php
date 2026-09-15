<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Models;

/**
 * Effective SRI environment for a transmission. The deployment ceiling
 * (APP_ENV) can never be escalated: production only reaches the real SRI,
 * staging only CELCER, and local never reaches the real SRI environment.
 */
enum SriEnvironment: string
{
    case Mock = 'mock';
    case Celcer = 'celcer';
    case Sri = 'sri';

    public static function fromConfigured(string $value): ?self
    {
        $normalized = strtolower(trim($value));

        return in_array($normalized, ['mock', 'celcer', 'sri'], true)
            ? self::from($normalized)
            : null;
    }

    public static function fromTenantMode(?string $mode): ?self
    {
        return match (strtolower(trim((string) $mode))) {
            'mockup', 'mock' => self::Mock,
            'celcer' => self::Celcer,
            'sri' => self::Sri,
            default => null,
        };
    }

    /** SRI ambiente code: '1' = pruebas (CELCER), '2' = produccion. */
    public function ambiente(): string
    {
        return $this === self::Sri ? '2' : '1';
    }
}
