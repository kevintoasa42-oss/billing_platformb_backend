<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;

final class ThirdPartyFieldDefinitionModel extends TenantScopedModel
{
    protected $connection = 'master_v3';

    protected $table = 'core.third_party_field_definitions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'code',
        'label',
        'scope',
        'data_type',
        'validation',
        'sort_order',
        'is_required',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'validation' => 'array',
            'sort_order' => 'integer',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
