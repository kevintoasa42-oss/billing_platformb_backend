<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstablishmentModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.establishments';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'company_id', 'sri_code', 'name',
        'branch_code', 'address', 'phone', 'email',
        'city_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(
            \App\Context\V3\Modules\Core\Company\Infrastructure\Laravel\Eloquent\Models\CompanyModel::class,
            'company_id'
        );
    }

    public function emissionPoints(): HasMany
    {
        return $this->hasMany(EmissionPointModel::class, 'establishment_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(EstablishmentActivityModel::class, 'establishment_id')
            ->whereRaw('core.establishment_activities.validity @> CURRENT_DATE');
    }
}
