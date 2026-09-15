<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for integration.consumer_inbox.
 */
final class ConsumerInboxModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'integration.consumer_inbox';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'id',
        'document_id',
        'operation',
        'status',
        'available_at',
    ];

    protected $casts = [
        'available_at' => 'datetime',
    ];
}
