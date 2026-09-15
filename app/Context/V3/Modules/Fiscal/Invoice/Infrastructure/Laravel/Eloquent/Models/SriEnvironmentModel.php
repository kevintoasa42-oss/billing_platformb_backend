<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for core.sri_environments.
 */
final class SriEnvironmentModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'core.sri_environments';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
    ];
}
