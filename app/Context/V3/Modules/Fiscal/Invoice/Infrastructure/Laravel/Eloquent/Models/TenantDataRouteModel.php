<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class TenantDataRouteModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'platform.tenant_data_routes';

    protected $primaryKey = 'tenant_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'state', 'frozen', 'updated_at',
    ];

    protected $casts = [
        'frozen' => 'boolean',
    ];
}
