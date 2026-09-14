<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SequenceModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'fiscal.sequences';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'environment', 'emission_point_id',
        'document_type', 'last_number',
    ];

    protected $casts = [
        'last_number' => 'integer',
    ];

    protected $attributes = [
        'last_number' => 0,
    ];

    public function emissionPoint(): BelongsTo
    {
        return $this->belongsTo(
            \App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel::class,
            'emission_point_id'
        );
    }
}
