<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;

class TenantSettingsModel extends TenantScopedModel
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $connection = 'master_v3';

    protected $table = 'core.tenant_settings';

    protected $primaryKey = 'tenant_id';

    public const CREATED_AT = null;

    protected $fillable = ['product_settings'];

    protected $casts = [
        'product_settings' => 'array',
    ];

    /**
     * Override HasUuids — tenant_id is the PK and is set by TenantScopedModel
     * from the request context, not auto-generated.
     */
    public function usesUniqueIds(): bool
    {
        return false;
    }
}
