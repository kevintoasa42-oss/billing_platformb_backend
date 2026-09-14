<?php

namespace App\Context\V3\Modules\Core\SriIva\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SriIvaTypeModel extends TenantScopedModel
{
    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $connection = 'master_v3';

    protected $table = 'core.sri_iva_types';

    protected $fillable = ['name', 'percentage', 'sri_code', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'percentage' => 'decimal:2',
    ];

    /**
     * Override HasUuids — this table uses a bigint IDENTITY column,
     * not a UUID primary key.
     */
    public function usesUniqueIds(): bool
    {
        return false;
    }

    public function percentages(): HasMany
    {
        return $this->hasMany(SriIvaPercentageModel::class, 'sri_iva_type_id', 'id');
    }
}
