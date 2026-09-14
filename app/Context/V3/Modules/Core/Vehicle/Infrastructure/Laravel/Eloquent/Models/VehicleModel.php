<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Vehicle\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;

class VehicleModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.vehicles';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'plate',
    ];
}
