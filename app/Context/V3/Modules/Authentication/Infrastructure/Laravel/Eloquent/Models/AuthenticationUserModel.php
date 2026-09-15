<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/** Eloquent read/write model for the auth.users table in master_v3. */
final class AuthenticationUserModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'auth.users';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'platform_admin' => 'boolean',
        'active' => 'boolean',
        'mfa_enabled' => 'boolean',
        'mfa_confirmed_at' => 'datetime',
    ];
}
