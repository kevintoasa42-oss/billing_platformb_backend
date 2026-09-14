<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarrierCompanyModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.carrier_companies';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'third_party_id', 'legal_name', 'trade_name', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyModel::class, 'third_party_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CarrierActivityModel::class, 'carrier_company_id');
    }
}
