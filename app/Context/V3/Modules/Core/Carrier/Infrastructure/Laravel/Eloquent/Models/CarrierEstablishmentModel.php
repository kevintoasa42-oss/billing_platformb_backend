<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarrierEstablishmentModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.carrier_establishments';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'tenant_id', 'id', 'carrier_company_id', 'sri_code',
        'name', 'address', 'phone', 'email', 'city_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function carrierCompany(): BelongsTo
    {
        return $this->belongsTo(CarrierCompanyModel::class, 'carrier_company_id');
    }

    public function emissionPoints(): HasMany
    {
        return $this->hasMany(CarrierEmissionPointModel::class, 'establishment_id');
    }
}
