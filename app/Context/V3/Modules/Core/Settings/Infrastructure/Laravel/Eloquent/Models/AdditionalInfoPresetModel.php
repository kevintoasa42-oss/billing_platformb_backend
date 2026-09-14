<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Laravel\Eloquent\Models;

use App\Context\V3\Shared\Infrastructure\Laravel\Eloquent\Models\TenantScopedModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AdditionalInfoPresetModel extends TenantScopedModel
{
    public $timestamps = true;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $connection = 'master_v3';

    protected $table = 'fiscal.additional_info_presets';

    protected $primaryKey = 'legacy_id';

    protected $fillable = [
        'id',
        'code',
        'name',
        'default_value',
        'auto_apply',
        'value_editable',
        'is_required',
        'is_active',
        'sort_order',
        'access_rules',
    ];

    protected $casts = [
        'auto_apply' => 'boolean',
        'value_editable' => 'boolean',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'access_rules' => 'array',
    ];

    /**
     * Override HasUuids — primary key is bigint IDENTITY (legacy_id), not UUID.
     * The `id` column is a UUID that we generate manually.
     */
    public function usesUniqueIds(): bool
    {
        return false;
    }
}
