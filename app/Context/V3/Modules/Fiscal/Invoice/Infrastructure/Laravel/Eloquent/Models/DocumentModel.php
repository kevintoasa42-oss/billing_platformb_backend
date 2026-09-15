<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'fiscal.documents';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id', 'environment', 'document_type', 'emission_point_id',
        'establishment_code', 'emission_point_code', 'sequential',
        'access_key', 'authorization_number', 'issued_at',
        'issuer_snapshot', 'recipient_snapshot',
        'subtotal', 'tax', 'discount', 'total',
        'fiscal_status', 'collection_status', 'delivery_status',
        'due_at', 'operation_id', 'carrier_id', 'vehicle_id',
        'plate_snapshot', 'writer_epoch',
        'legacy_sri_status', 'legacy_internal_status',
        'legacy_fiscal_status', 'legacy_fiscal_provider',
        'source_database', 'not_valid_for_sri', 'fiscal_status_summary',
    ];

    protected $casts = [
        'issuer_snapshot' => 'array',
        'recipient_snapshot' => 'array',
        'issued_at' => 'datetime',
        'not_valid_for_sri' => 'boolean',
        'sequential' => 'integer',
        'writer_epoch' => 'integer',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLineModel::class, 'document_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DocumentPaymentModel::class, 'document_id', 'id');
    }
}
