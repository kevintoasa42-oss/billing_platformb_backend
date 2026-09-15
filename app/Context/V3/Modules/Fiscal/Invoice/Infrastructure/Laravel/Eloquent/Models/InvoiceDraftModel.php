<?php

namespace App\Context\V3\Modules\Fiscal\Invoice\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;

class InvoiceDraftModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'fiscal.invoice_drafts';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'id', 'public_id', 'user_id', 'revision',
        'payload', 'expires_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'revision' => 'integer',
        'expires_at' => 'datetime',
    ];
}
