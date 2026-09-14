<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/** Eloquent model for the platform.tenants catalog in master_v3. */
final class AuthenticationEnterpriseModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'platform.tenants';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;
}
