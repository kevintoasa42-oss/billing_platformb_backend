<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductModel extends TenantScopedModel
{
    public $timestamps = false;

    protected $connection = 'master_v3';

    protected $table = 'core.products';

    protected $fillable = [
        'name', 'activity_id', 'unit_price',
        'barcode', 'auxiliary_code', 'other_code',
        'description', 'type', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'unit_price' => 'decimal:6',
    ];

    public function taxAssignments(): HasMany
    {
        return $this->hasMany(ProductTaxAssignmentModel::class, 'product_id', 'id');
    }
}
