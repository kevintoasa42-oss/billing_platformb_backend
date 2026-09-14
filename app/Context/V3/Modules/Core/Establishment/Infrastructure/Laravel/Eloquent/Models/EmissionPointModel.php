<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmissionPointModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.emission_points';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'establishment_id', 'sri_code',
        'name', 'is_active', 'is_default', 'has_tax_validity',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_default' => false,
        'has_tax_validity' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'has_tax_validity' => 'boolean',
    ];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(EstablishmentModel::class, 'establishment_id');
    }
}
