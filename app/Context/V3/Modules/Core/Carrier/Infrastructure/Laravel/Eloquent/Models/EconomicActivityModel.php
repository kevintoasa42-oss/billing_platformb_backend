<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for core.economic_activities (global catalog, no RLS).
 */
class EconomicActivityModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'core.economic_activities';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id', 'name', 'catalog_version',
    ];
}
