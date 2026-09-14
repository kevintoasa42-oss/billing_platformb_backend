<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/** Eloquent model for tenant-scoped auth.tenant_memberships records. */
final class AuthenticationMembershipModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'auth.tenant_memberships';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'active' => 'boolean',
        'authorization_version' => 'integer',
        'capabilities' => 'array',
    ];
}
