<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Mappers;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\ElectronicSignature;
use App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models\EnterpriseElectronicSignatureModel;

/**
 * Maps an EnterpriseElectronicSignatureModel (Eloquent) into a Domain ElectronicSignature.
 * Performs no database queries.
 */
final class ElectronicSignatureMapper
{
    public function toDomain(EnterpriseElectronicSignatureModel $record): ElectronicSignature
    {
        return new ElectronicSignature(
            id: (string) $record->id,
            companyId: (string) $record->company_id,
            hasElectronicSignature: (bool) $record->has_electronic_signature,
            sriEnvironmentId: $record->sri_environment_id !== null ? (int) $record->sri_environment_id : null,
            sriMode: (string) $record->sri_mode,
            fileType: $record->file_type,
            fileName: $record->file_name,
            password: $record->password,
            expirationDate: $record->expiration_date?->format('Y-m-d'),
            maxInvoicesPerMonth: (int) $record->max_invoices_per_month,
            keyBackend: (string) ($record->key_backend ?? 'legacy_p12'),
            keyReference: $record->key_reference,
            keyVersion: $record->key_version !== null ? (int) $record->key_version : null,
            certificatePem: $record->certificate_pem,
            certificateChain: is_array($record->certificate_chain) ? $record->certificate_chain : null,
            fingerprintSha256: $record->fingerprint_sha256,
            certificateSerial: $record->certificate_serial,
            certificateSubject: $record->certificate_subject,
            certificateIssuer: $record->certificate_issuer,
            certificateRuc: $record->certificate_ruc,
            validFrom: $record->valid_from?->toIso8601String(),
            validUntil: $record->valid_until?->toIso8601String(),
            keyAlgorithm: $record->key_algorithm,
            keyBits: $record->key_bits !== null ? (int) $record->key_bits : null,
            validationStatus: (string) $record->validation_status,
            migrationStatus: (string) $record->migration_status,
            credentialVersion: (int) $record->credential_version,
        );
    }
}
