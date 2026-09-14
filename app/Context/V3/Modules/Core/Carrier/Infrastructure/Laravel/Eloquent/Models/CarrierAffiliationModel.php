<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarrierAffiliationModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.carrier_affiliations';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'third_party_id', 'validity',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyModel::class, 'third_party_id');
    }

    public function vehicleAssignments(): HasMany
    {
        return $this->hasMany(CarrierVehicleAssignmentModel::class, 'affiliation_id');
    }
}
