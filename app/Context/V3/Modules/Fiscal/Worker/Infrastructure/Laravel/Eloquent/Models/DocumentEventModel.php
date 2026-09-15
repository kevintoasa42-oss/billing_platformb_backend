<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for fiscal.document_events.
 */
final class DocumentEventModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'fiscal.document_events';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'id',
        'document_id',
        'event',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
