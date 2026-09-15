<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;

class OperationModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'fiscal.operations';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'id', 'document_id', 'actor_id', 'action',
        'status', 'idempotency_key',
    ];
}
