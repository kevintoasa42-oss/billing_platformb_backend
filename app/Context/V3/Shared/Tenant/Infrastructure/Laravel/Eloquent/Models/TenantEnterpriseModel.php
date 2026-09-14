<?php

namespace App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/** Read model for the landlord enterprise database mapping. */
final class TenantEnterpriseModel extends Model
{
    protected $connection = 'master_v3';

    protected $table = 'enterprises';
}
