<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for integration.processing_jobs.
 */
final class ProcessingJobModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'integration.processing_jobs';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'id',
        'document_id',
        'operation',
        'predecessor_id',
        'status',
        'available_at',
        'lease_until',
        'fencing_token',
        'writer_epoch',
        'attempts',
        'max_attempts',
    ];

    protected $casts = [
        'available_at' => 'datetime',
        'lease_until' => 'datetime',
        'fencing_token' => 'integer',
        'writer_epoch' => 'integer',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
    ];
}
