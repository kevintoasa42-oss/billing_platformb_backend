<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThirdPartyRoleModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.third_party_roles';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'third_party_id', 'role',
    ];

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdPartyModel::class, 'third_party_id');
    }
}
