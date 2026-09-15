<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for core.third_party_roles.
 */
class ThirdPartyRoleModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'core.third_party_roles';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'id', 'third_party_id', 'role'];
}
