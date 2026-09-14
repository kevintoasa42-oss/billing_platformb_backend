<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierActivityModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.carrier_activities';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'carrier_company_id', 'activity_id', 'validity', 'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function carrierCompany(): BelongsTo
    {
        return $this->belongsTo(CarrierCompanyModel::class, 'carrier_company_id');
    }

    public function economicActivity(): BelongsTo
    {
        return $this->belongsTo(EconomicActivityModel::class, 'activity_id');
    }
}
