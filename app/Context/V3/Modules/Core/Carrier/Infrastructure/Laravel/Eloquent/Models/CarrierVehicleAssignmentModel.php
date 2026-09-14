<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierVehicleAssignmentModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.carrier_vehicle_assignments';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'affiliation_id', 'vehicle_id', 'validity',
    ];

    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(CarrierAffiliationModel::class, 'affiliation_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_id');
    }
}
