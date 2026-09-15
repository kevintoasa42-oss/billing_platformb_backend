<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTaxAssignmentModel extends TenantScopedModel
{
    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $connection = 'master_v3';

    protected $table = 'core.product_tax_assignments';

    protected $fillable = [
        'product_id', 'sri_iva_type_id', 'tax_name',
        'percentage', 'sri_code', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'percentage' => 'decimal:6',
    ];

    /**
     * Override HasUuids — this table uses a uuid PK with legacy_id IDENTITY.
     */
    public function usesUniqueIds(): bool
    {
        return true;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductModel::class, 'product_id', 'id');
    }
}
