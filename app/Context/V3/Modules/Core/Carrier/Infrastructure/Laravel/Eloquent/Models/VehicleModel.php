<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for core.vehicles (relation target only).
 */
class VehicleModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'core.vehicles';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'plate', 'legacy_id',
    ];
}
