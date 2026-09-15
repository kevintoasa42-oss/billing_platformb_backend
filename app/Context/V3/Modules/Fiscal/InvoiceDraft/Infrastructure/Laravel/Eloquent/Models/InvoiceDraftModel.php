<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;

final class InvoiceDraftModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'fiscal.invoice_drafts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'public_id',
        'user_id',
        'revision',
        'payload',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'revision' => 'integer',
            'payload' => 'array',
            'expires_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
