<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for integration.processing_attempts.
 */
final class ProcessingAttemptModel extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'integration.processing_attempts';

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'id',
        'job_id',
        'attempt_number',
        'status',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'attempt_number' => 'integer',
    ];
}
