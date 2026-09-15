<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;

final class ThirdPartyFieldValueModel extends TenantScopedModel
{
    public $timestamps = false;

    protected $connection = 'master_v3';

    protected $table = 'core.third_party_field_values';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'third_party_id',
        'definition_id',
        'value',
        'updated_by',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
