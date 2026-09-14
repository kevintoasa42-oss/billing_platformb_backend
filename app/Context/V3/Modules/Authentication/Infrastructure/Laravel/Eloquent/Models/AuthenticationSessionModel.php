<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/** Eloquent model for tenant-scoped auth.sessions records. */
final class AuthenticationSessionModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'auth.sessions';

    protected $primaryKey = 'token_hash';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = [
        'authorization_version' => 'integer',
        'expires_at' => 'datetime',
    ];
}
