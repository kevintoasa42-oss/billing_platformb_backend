<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThirdPartyModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.third_parties';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'name', 'identification', 'must_invoice',
        'legacy_id', 'identification_type', 'person_type', 'address', 'phone', 'email',
        'customer_type_id', 'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
        'must_invoice' => true,
    ];

    protected $casts = [
        'must_invoice' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(ThirdPartyActivityModel::class, 'third_party_id');
    }

    public function roles(): HasMany
    {
        return $this->hasMany(ThirdPartyRoleModel::class, 'third_party_id');
    }
}
