<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Company\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.companies';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'name', 'ruc', 'legal_name', 'trade_name',
        'matrix_address', 'operations_start_date', 'city_id', 'phone', 'corporate_email',
    ];

    protected $casts = [
        'operations_start_date' => 'date',
    ];

    public function establishments(): HasMany
    {
        return $this->hasMany(
            \App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel::class,
            'company_id'
        );
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CompanyActivityModel::class, 'tenant_id', 'tenant_id')
            ->whereRaw('core.company_activities.validity @> CURRENT_DATE');
    }
}
