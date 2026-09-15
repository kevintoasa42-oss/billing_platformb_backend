<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for core.enterprise_tax_settings.
 */
final class EnterpriseTaxSettingModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'core.enterprise_tax_settings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'id',
        'company_id',
        'has_accounting',
        'has_inventory',
        'authorization_mode',
    ];

    protected $casts = [
        'has_accounting' => 'boolean',
        'has_inventory' => 'boolean',
    ];
}
