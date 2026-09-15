<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for core.enterprise_electronic_signatures.
 */
final class EnterpriseElectronicSignatureModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'core.enterprise_electronic_signatures';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'id',
        'company_id',
        'has_electronic_signature',
        'sri_environment_id',
        'sri_mode',
        'file_type',
        'file_name',
        'password',
        'expiration_date',
        'max_invoices_per_month',
        'key_backend',
        'key_reference',
        'key_version',
        'certificate_pem',
        'certificate_chain',
        'fingerprint_sha256',
        'certificate_serial',
        'certificate_subject',
        'certificate_issuer',
        'certificate_ruc',
        'valid_from',
        'valid_until',
        'key_algorithm',
        'key_bits',
        'validation_status',
        'migration_status',
        'credential_version',
        'custody_verified_at',
        'migrated_at',
        'custody_last_signed_at',
        'legacy_last_used_at',
        'custody_celcer_authorized_at',
        'custody_celcer_evidence_sha256',
        'legacy_retired_at',
    ];

    protected $casts = [
        'has_electronic_signature' => 'boolean',
        'expiration_date' => 'date',
        'certificate_chain' => 'array',
        'valid_from' => 'immutable_datetime',
        'valid_until' => 'immutable_datetime',
        'custody_verified_at' => 'immutable_datetime',
        'migrated_at' => 'immutable_datetime',
        'custody_last_signed_at' => 'immutable_datetime',
        'legacy_last_used_at' => 'immutable_datetime',
        'custody_celcer_authorized_at' => 'immutable_datetime',
        'legacy_retired_at' => 'immutable_datetime',
        'key_version' => 'integer',
        'key_bits' => 'integer',
        'max_invoices_per_month' => 'integer',
        'credential_version' => 'integer',
    ];

    protected $hidden = [
        'password',
        'file_name',
        'key_reference',
        'certificate_pem',
        'certificate_chain',
    ];
}
