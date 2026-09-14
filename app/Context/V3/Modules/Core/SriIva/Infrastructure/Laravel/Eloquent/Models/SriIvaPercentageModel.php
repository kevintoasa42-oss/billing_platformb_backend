<?php

namespace App\Context\V3\Modules\Core\SriIva\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SriIvaPercentageModel extends TenantScopedModel
{
    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $connection = 'master_v3';

    protected $table = 'core.sri_iva_percentages';

    protected $fillable = ['sri_iva_type_id', 'percentage', 'start_date', 'end_date', 'code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Override HasUuids — this table uses a bigint IDENTITY column,
     * not a UUID primary key.
     */
    public function usesUniqueIds(): bool
    {
        return false;
    }

    public function sriIvaType(): BelongsTo
    {
        return $this->belongsTo(SriIvaTypeModel::class, 'sri_iva_type_id', 'id');
    }
}
