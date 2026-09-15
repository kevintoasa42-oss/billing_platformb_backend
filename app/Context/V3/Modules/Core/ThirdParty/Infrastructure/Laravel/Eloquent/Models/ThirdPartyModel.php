<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ThirdPartyModel extends TenantScopedModel
{
    public $timestamps = false;

    protected $connection = 'master_v3';

    protected $table = 'core.third_parties';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'identification',
        'identification_type',
        'person_type',
        'must_invoice',
        'legacy_id',
        'address',
        'phone',
        'email',
        'customer_type_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'must_invoice' => 'boolean',
            'is_active' => 'boolean',
            'legacy_id' => 'integer',
            'customer_type_id' => 'integer',
        ];
    }

    public function roles(): HasMany
    {
        return $this->hasMany(ThirdPartyRoleModel::class, 'third_party_id', 'id');
    }
}
