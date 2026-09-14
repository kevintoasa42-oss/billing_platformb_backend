<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstablishmentActivityModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.establishment_activities';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'id', 'establishment_id', 'activity_id', 'validity',
    ];

    public function economicActivity(): BelongsTo
    {
        return $this->belongsTo(
            \App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Laravel\Eloquent\Models\EconomicActivityModel::class,
            'activity_id'
        );
    }
}
