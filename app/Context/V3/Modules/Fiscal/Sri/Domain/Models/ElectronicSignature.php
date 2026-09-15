<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Domain\Models;

/**
 * Plain PHP domain model for an enterprise electronic signature.
 * Does not depend on Eloquent, DB, or Illuminate.
 */
final class ElectronicSignature
{
    public const KEY_BACKEND_LEGACY = 'legacy_p12';
    public const KEY_BACKEND_OPENBAO = 'openbao_transit';

    public const MIGRATION_LEGACY = 'legacy';
    public const MIGRATION_SHADOW_VERIFIED = 'shadow_verified';
    public const MIGRATION_ACTIVE = 'active';
    public const MIGRATION_ROLLED_BACK = 'rolled_back';
    public const MIGRATION_FAILED = 'failed';

    public function __construct(
        public readonly string $id,
        public readonly string $companyId,
        public readonly bool $hasElectronicSignature,
        public readonly ?int $sriEnvironmentId,
        public readonly string $sriMode,
        public readonly ?string $fileType,
        public readonly ?string $fileName,
        public readonly ?string $password,
        public readonly ?string $expirationDate,
        public readonly int $maxInvoicesPerMonth,
        public readonly string $keyBackend,
        public readonly ?string $keyReference,
        public readonly ?int $keyVersion,
        public readonly ?string $certificatePem,
        public readonly ?array $certificateChain,
        public readonly ?string $fingerprintSha256,
        public readonly ?string $certificateSerial,
        public readonly ?string $certificateSubject,
        public readonly ?string $certificateIssuer,
        public readonly ?string $certificateRuc,
        public readonly ?string $validFrom,
        public readonly ?string $validUntil,
        public readonly ?string $keyAlgorithm,
        public readonly ?int $keyBits,
        public readonly string $validationStatus,
        public readonly string $migrationStatus,
        public readonly int $credentialVersion,
    ) {}

    public function usesOpenBao(): bool
    {
        return $this->keyBackend === self::KEY_BACKEND_OPENBAO
            && $this->migrationStatus === self::MIGRATION_ACTIVE;
    }

    public function canUseLegacySigner(): bool
    {
        return $this->keyBackend === self::KEY_BACKEND_LEGACY;
    }

    public function getEffectiveSriMode(): string
    {
        if (in_array($this->sriMode, ['mockup', 'mock', 'celcer', 'sri'], true)) {
            return $this->sriMode;
        }

        return 'celcer';
    }
}
