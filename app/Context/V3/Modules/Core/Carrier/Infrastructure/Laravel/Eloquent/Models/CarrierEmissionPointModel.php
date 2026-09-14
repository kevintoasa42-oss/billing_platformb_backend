<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierEmissionPointModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.carrier_emission_points';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'establishment_id', 'sri_code',
        'name', 'next_sequential', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'next_sequential' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'next_sequential' => 1,
    ];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(CarrierEstablishmentModel::class, 'establishment_id');
    }
}
